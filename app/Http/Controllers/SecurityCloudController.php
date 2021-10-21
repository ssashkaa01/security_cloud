<?php

namespace App\Http\Controllers;

use App\Models\SecurityCloud;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SecurityCloudController extends Controller
{
    // Сторінка для роботи з даними
    public function Main(Request $request) {

        return view('main');
    }

    // Отримання даних
    public function GetData(Request $request) {

        $validator = Validator::make($request->all(), [
            'key' => ['required', 'string']
        ]);

        if ($validator->errors()->isNotEmpty()) {
            abort(403);
        }

        // Пошук даних по ключу
        $searchResult = SecurityCloud::query()
            ->whereRaw('hash = encode(sha256(concat(info, ?::text)::bytea), \'hex\')::text', [$request->key])
            ->first();

        if (empty($searchResult)) {
            abort(403);
        }

        // Добування даних з інфо блоку
        $blockInfo = json_decode(base64_decode($searchResult->info));

        // Добування даних з ключа
        $blockKey = json_decode(SecurityCloud::Decrypt(base64_decode($request->key), $blockInfo->key_password, $blockInfo->key_iv));

        $fileData = Storage::disk('local')->get("data/{$blockKey->file_name}");
        $fileDataDecrypted = SecurityCloud::Decrypt($fileData, $blockKey->file_password, $blockInfo->file_iv);

        return response()->json([
            'data' => $fileDataDecrypted
        ]);
    }

    // Шифрування даних
    public function Crypt(Request $request) {

        $validator = Validator::make($request->all(), [
            'key' => ['nullable', 'string'],
            'data' => ['required', 'string']
        ]);

        if ($validator->errors()->isNotEmpty()) {
            abort(400);
        }

        // Якщо ключа не знайдено, записуємо новий файл
        if(empty($request->key)) {

            $fileName = uniqid().'_'.time().'.data';
            $password = SecurityCloud::GenerateString(128);
            $passwordBlockKey = SecurityCloud::GenerateString(128);

            // Шифруємо вміст
            $encrypted = SecurityCloud::Encrypt($request->data, $password);
            $encryptedBlockKey = SecurityCloud::Encrypt(json_encode([
                'file_password' => $password,
                'file_name' => $fileName
            ]), $passwordBlockKey);

            $blockInfo = base64_encode(json_encode([
                'key_password' => $passwordBlockKey,
                'key_iv' => $encryptedBlockKey->iv,
                'file_iv' => $encrypted->iv
            ]));

            $blockKey = base64_encode($encryptedBlockKey->data);
            $blockHash = hash('sha256', $blockInfo.$blockKey);

            // Запис у файл
            Storage::disk('local')->put("data/{$fileName}", $encrypted->data);

            // Запис у бд
            SecurityCloud::query()->create([
                'hash' => $blockHash,
                'info' => $blockInfo
            ]);
        }
        else {
            // Пошук даних по ключу
            $searchResult = SecurityCloud::query()
                ->whereRaw('hash = encode(sha256(concat(info, ?::text)::bytea), \'hex\')::text', [$request->key])
                ->first();

            if (empty($searchResult)) {
                abort(403);
            }

            // Добування даних з інфо блоку
            $blockInfo = json_decode(base64_decode($searchResult->info));

            // Добування даних з ключа
            $blockKey = json_decode(SecurityCloud::Decrypt(base64_decode($request->key), $blockInfo->key_password, $blockInfo->key_iv));

            // Шифруємо вміст
            $encrypted = SecurityCloud::Encrypt($request->data, $blockKey->file_password);

            $blockInfo->file_iv = $encrypted->iv;

            // Запис у файл
            Storage::disk('local')->put("data/{$blockKey->file_name}", $encrypted->data);

            $blockInfo = base64_encode(json_encode($blockInfo));
            $blockHash = hash('sha256', $blockInfo.$request->key);

            // Запис у бд
            $searchResult->update([
                'hash' => $blockHash,
                'info' => $blockInfo
            ]);

            $blockKey = '';
        }

        return response()->json([
            'key' => $blockKey
        ]);
    }
}

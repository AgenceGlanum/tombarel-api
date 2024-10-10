<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class DocumentsController extends Controller
{
    public function folders(Request $request): \Illuminate\Http\JsonResponse|array
    {
        $data = $request->validate([
            "user_id" => "required", 'integer'
        ]);

        $user = User::query()->where('id', $data['user_id'])->first();
        $userRequests = \App\Models\Request::query()->where('user_id', $user->id)->pluck('id');
        $folders = Document::query()->whereIn('request_id', $userRequests)->get();

        if ($folders->count() > 0) {
            return ['folders' => $folders];
        }

        return response()->json(['message' => 'Aucun fichier n\'a été trouvé'], 200);
    }

    public function searchFolders(Request $request): \Illuminate\Http\JsonResponse
    {
        //faire appel à l'api genapi pour cette route et récupérer les dossiers par keyword
        $data = $request->validate([
            "tenant_id" => "required", 'integer',
            "user_id" => "required", 'integer',
            "keyword" => "required", 'string'
        ]);

        if (!$data['tenant_id']) {
            return response()->json(['message' => 'Une erreur s\'est produite'], 405);
        }

        $token = PersonalAccessToken::query()
            ->where('tokenable_id', $data['user_id'])
            ->where('tokenable_type', User::class)
            ->latest()
            ->first()
            ->token;

        $folders = $this->getFoldersWithKeyword($data['tenant_id'], $token, $data['keyword']);

        return response()->json(['folders' => $folders]);
    }

    public function getFoldersWithKeyword($tenant_id, $token, $keyword): array
    {
        $headers = [
            'Authorization: Bearer ' . $token,
            'TenantId: ' . $tenant_id
        ];

        $start = 0;
        $length = 50;
        $all_folders = [];
        $url = env('URL_GENAPI') . "/Search?start=$start&length=$length&search=" . urlencode($keyword);

        while (true) {
            $response = $this->make_request($url, $headers);

            if ($response['status_code'] == 200) {
                $folders_data = json_decode($response['body'], true);
                $all_folders = array_merge($all_folders, extract_folders($folders_data));
                $start += $length;

                if ($start >= $folders_data['total']) {
                    break;
                }

                usleep(200000); // pause for 200ms
            } else {
                throw new \Exception("Error: {$response['status_code']} - {$response['body']}");
            }
        }

        return $all_folders;
    }

    function make_request($url, $headers)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ['status_code' => $http_code, 'body' => $response];
    }

    function extract_folders($response_data)
    {
        $folders_list = [];
        foreach ($response_data['items'] as $item) {
            $folders_list[] = [
                'id' => $item['id'] ?? null,
                'designation' => $item['intitule'] ?? null,
                'name' => $item['nom'] ?? null
            ];
        }

        return $folders_list;
    }

    public function insertRequest(Request $request, $folderId)
    {
        $data = $request->validate([
            "tenant_id" => "required", "integer",
            "user_id" => "required", 'integer'
        ]);

        $request = new \App\Models\Request();
        $request->fill([
            'user_id' => $data['user_id'],
            'tenant_id' => $data['tenant_id'],
            'folder_id' => $folderId,
        ]);

        $request->save();

        return ['request' => $request];
    }

    public function folderById(Request $request, $folder_id)
    {
        $data = $request->validate([
            "user_id" => "required", 'integer',
        ]);

        if (!$data['user_id']) {
            return response()->json(['message' => 'Une erreur s\'est produite'], 405);
        }

//        $token = PersonalAccessToken::query()
//            ->where('tokenable_id', $data['user_id'])
//            ->where('tokenable_type', User::class)
//            ->latest()
//            ->first()
//            ->token;

        $requestId = \App\Models\Request::query()->where('folder_id', $folder_id)->first()->id;
        $folder = Document::query()->where('request_id', $requestId)->first();

        return response()->json(['folder' => $folder]);
    }

    public function folderByRequest(Request $request, $requestId)
    {
        $data = $request->validate([
            "user_id" => "required", 'integer',
        ]);

        if (!$data['user_id']) {
            return response()->json(['message' => 'Une erreur s\'est produite'], 405);
        }

        $folders = Document::query()->where('request_id', $requestId)->get();

        return response()->json(['folders' => $folders]);
    }
}

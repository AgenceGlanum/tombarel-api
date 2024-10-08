<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\User;
use Illuminate\Http\Request;

class DocumentsController extends Controller
{
    public function folders(Request $request)
    {
        $data = $request->validate([
            "user_id" => "required",'integer'
        ]);

        $user = User::query()->where('id', $data['user_id'])->first();
        $userRequests = \App\Models\Request::query()->where('user_id', $user->id)->pluck('id');
        $folders = Document::query()->whereIn('request_id', $userRequests)->get();

        if($folders->count() > 0){
            return ['folders' => $folders];
        }

        return response()->json(['message' => 'Aucun fichier n\'a été trouvé'], 200);
    }

    public function searchFolders(Request $request)
    {
        //faire appel à l'api genapi pour cette route et récupérer les dossiers par keyword
        $data = $request->validate([
            "tenant_id" => "required",'integer',
            "token" => "required",'string',
            "keyword" => "required",'string'
        ]);

        if(!$data['tenant_id']){
            return response()->json(['message' => 'Une erreur s\'est produite'], 405);
        }
        
        dd($data['tenant_id'], $data['token'], $data['keyword']);

        $folders = $this->getFoldersWithKeyword($data['tenant_id'], $data['token'], $data['keyword']);

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
        $url = env('URL_GENAPI')."/search";

        do {
            // Setup the query parameters
            $params = http_build_query([
                'start' => $start,
                'length' => $length,
                'search' => $keyword
            ]);

            // Setup cURL request
            $ch = curl_init($url . '?' . $params);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            // Execute the request
            $response = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Handle response
            if ($status_code == 200) {
                $folders_data = json_decode($response, true);
                $all_folders = array_merge($all_folders, extract_folders($folders_data)); // Assumes you have an extract_folders function
                usleep(200000); // Sleep for 0.2 seconds
                $start += $length;

                // Break if we've processed all results
                if ($start >= $folders_data['total']) {
                    break;
                }
            } else {
                throw new Exception("Error {$status_code}: " . $response);
            }
        } while (true);

        return $all_folders;
    }

// Dummy function to handle folder extraction
    function extract_folders($folders_data) {
        // Implement this logic based on how folders are structured in the API response
        return $folders_data['folders'] ?? [];
    }

    public function insertRequest(Request $request, $id)
    {
        $data = $request->validate([
            "tenant_id" => "required","integer",
            "user_id" => "required",'integer'
        ]);
        //modifier ou insert le tenantId de l'user
        $user = User::query()->where('id', $data['user_id'])->first();
        $user->tenant_id = $data['tenant_id'];
        $user->save();

        return ['user' => $user,'tenant_id' => $user->tenant_id ?? null];
    }

    public function folderByRequest(Request $request, $id)
    {
        $data = $request->validate([
            "tenant_id" => "required","integer",
            "user_id" => "required",'integer'
        ]);
        //modifier ou insert le tenantId de l'user
        $user = User::query()->where('id', $data['user_id'])->first();
        $user->tenant_id = $data['tenant_id'];
        $user->save();

        return ['user' => $user,'tenant_id' => $user->tenant_id ?? null];
    }
}

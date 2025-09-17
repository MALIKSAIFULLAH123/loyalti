<?php

namespace MetaFox\Firebase\Support;

use Illuminate\Support\Facades\Log;
use MetaFox\Platform\Facades\Settings;
use MrShan0\PHPFirestore\Fields\FireStoreObject;
use MrShan0\PHPFirestore\FireStoreApiClient;
use MrShan0\PHPFirestore\FireStoreDocument;

class Firestore
{
    protected FireStoreApiClient $firestoreClient;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $project = Settings::get('firebase.project_id');
        $apiKey  = Settings::get('firebase.api_key');
        if (!$project || !$apiKey) {
            return false;
        }
        try {
            $this->firestoreClient = new FireStoreApiClient($project, $apiKey, [

            ]);
        } catch (\Exception $e) {
            throw new $e();
        }

        return true;
    }

    /**
     * @param  string               $collection
     * @param  array<string, mixed> $params
     * @param  string               $documentId
     * @return bool
     */
    public function addDocument(string $collection, string $documentId, array $params): bool
    {
        try {
            $result = $this->firestoreClient->setDocument($collection, $documentId, $params);
            $result = json_decode($result, true);
            if (isset($result['error'])) {
                Log::error($result['error']['message'] ?? 'Error when save document: ' . $documentId);

                return false;
            }
        } catch (\Exception $e) {
            Log::error($e);

            return false;
        }

        return true;
    }

    /**
     * @param  string               $collection
     * @param  string               $documentId
     * @param  array<string, mixed> $params
     * @return bool
     */
    public function updateDocument(string $collection, string $documentId, array $params): bool
    {
        try {
            $result = $this->firestoreClient->setDocument($collection, $documentId, $params, ['exists' => true, 'merge' => true]);
            $result = json_decode($result, true);
            if (isset($result['error'])) {
                Log::error($result['error']['message'] ?? 'Error when update document: ' . $documentId);

                return false;
            }
        } catch (\Exception $e) {
            Log::error($e);

            return false;
        }

        return true;
    }

    /**
     * @param  string $collection
     * @param  string $documentId
     * @return array
     */
    public function getDocument(string $collection, string $documentId): array
    {
        try {
            $result = $this->firestoreClient->getDocument($collection, $documentId);
            if ($result instanceof FireStoreDocument) {
                return $this->castValues($result->toArray());
            }
        } catch (\Exception $e) {
        }

        return [];
    }

    /**
     * @param  array $document
     * @return array
     */
    private function castValues(array $document): array
    {
        foreach ($document as $key => $data) {
            if (is_array($data)) {
                $document[$key] = $this->castValues($data);
            }
            if ($data instanceof FireStoreObject) {
                $array          = $data->getData();
                $document[$key] = new FireStoreObject($this->castValues(reset($array)));
            }
        }

        return $document;
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class GalleryModel extends Model
{
    protected $table = 'int_saksiam_gallery';
    protected $primaryKey = 'int_saksiam_gallery_ID';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $protectFields = true;

    protected $allowedFields = [
        'int_saksiam_gallery_path',
        'int_saksiam_gallery_type',
        'int_saksiam_gallery_namepage',
        'int_saksiam_gallery_thumbnail',
        'int_saksiam_gallery_filesize',
        'int_saksiam_gallery_extensin',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'int_saksiam_gallery_creatreAt';
    protected $updatedField = 'int_saksiam_gallery_updateAt';

    protected $beforeInsert = ['handleInsertTimestamps'];
    protected $beforeUpdate = ['handleUpdateTimestamps'];

    protected function handleInsertTimestamps(array $data)
    {
        $data['data']['int_saksiam_gallery_creatreAt'] = date('Y-m-d H:i:s');
        unset($data['data']['int_saksiam_gallery_updateAt']);

        return $data;
    }

    protected function handleUpdateTimestamps(array $data)
    {
        $data['data']['int_saksiam_gallery_updateAt'] = date('Y-m-d H:i:s');
        unset($data['data']['int_saksiam_gallery_creatreAt']);

        return $data;
    }

    public function getGalleryData($offset = 0, $limit = 50, $type = null, $namepage = null)
    {
        $this->baseSelect()->orderBy('int_saksiam_gallery_ID', 'DESC');

        if ($type !== null) {
            $this->where('int_saksiam_gallery_type', $type);
        }

        if ($namepage !== null) {
            $this->where('int_saksiam_gallery_namepage', $namepage);
        }

        $query = $this->findAll($limit, $offset);

        $countBuilder = $this->builder();
        if ($type !== null) {
            $countBuilder->where('int_saksiam_gallery_type', $type);
        }

        if ($namepage !== null) {
            $countBuilder->where('int_saksiam_gallery_namepage', $namepage);
        }

        return [
            'counts' => (int) $countBuilder->countAllResults(),
            'gallery' => $query,
            'galleries' => $query,
        ];
    }

    public function createGallery($data)
    {
        if (empty($data)) {
            throw new \Exception('Data is required');
        }

        $data['int_saksiam_gallery_creatreAt'] = date('Y-m-d H:i:s');
        $this->insert($data);

        return $this->getInsertID();
    }

    public function updateGallery($id, $data)
    {
        if (!$this->find($id)) {
            return false;
        }

        return $this->update($id, $data);
    }

    public function showGallery($id = null)
    {
        $this->baseSelect();

        if ($id !== null) {
            return $this->find($id);
        }

        return $this->orderBy('int_saksiam_gallery_ID', 'DESC')->findAll();
    }

    private function baseSelect()
    {
        return $this->select('
            int_saksiam_gallery_ID,
            int_saksiam_gallery_path,
            int_saksiam_gallery_type,
            int_saksiam_gallery_namepage,
            int_saksiam_gallery_thumbnail,
            int_saksiam_gallery_filesize,
            int_saksiam_gallery_extensin,
            int_saksiam_gallery_creatreAt,
            int_saksiam_gallery_updateAt,
            int_saksiam_gallery_ID AS id,
            int_saksiam_gallery_path AS path,
            int_saksiam_gallery_type AS type,
            int_saksiam_gallery_namepage AS namepage,
            int_saksiam_gallery_thumbnail AS thumbnail,
            int_saksiam_gallery_filesize AS filesize,
            int_saksiam_gallery_extensin AS extensin,
            int_saksiam_gallery_creatreAt AS createAt,
            int_saksiam_gallery_updateAt AS updateAt
        ');
    }
}

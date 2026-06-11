<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Controller;
use App\Models\TopicModel;

class ControllerTopic extends BaseController
{
  protected $TopicModel;

    public function __construct()
    {
        $this->topic = new TopicModel();
    }

    public function showTopicdetaillist()
    {
        $showdata = $this->topic->getTopicShow();
        return $this->response->setJSON([
            'status' => true,
            'message' => 'Data Topic retrieved successfully',
            'result' => $showdata,
            'data' => $showdata,
        ]);
    }

    public function showTopicData()
    {
        $activeFilter = $this->request->getGet('active');
        $limit = $this->request->getVar('limit') ?? 50;
        $offset = $this->request->getVar('offset') ?? 0;
        if ($activeFilter === "") {
            $activeFilter = null;
        } else {
            // Otherwise, cast it to integer for safety
            $activeFilter = (int) $activeFilter;
            // Ensure the activ7eFilter is either 0 or 1
            if (!in_array($activeFilter, [0, 1])) {
                return $this->response->setJSON([
                    'status' => 400,
                    'message' => 'Invalid active filter value'
                ])->setStatusCode(400);
            }
        }

        $topics = $this->topic->getTopicData($activeFilter, $offset, $limit);

        if ($topics) {
            return $this->response->setJSON([
                'status' => 200,
                'data' => $topics,
            ]);
        } else {
            return $this->response->setJSON(0);
        }
    }

    public function upTopicDataAPI()
    {
        $jsonData = $this->request->getJSON(true);

        $topicnameTH = $jsonData['nameTH'] ?? null;
        $topicnameEN = $jsonData['nameEN'] ?? null;
        $topicnsavename = $jsonData['savename'] ?? null;
        $topicactive = $jsonData['active'] ?? null;

        if (!$topicnameTH || !$topicnameEN) {
            return $this->response->setJSON(['error' => 'กรุณากรอกข้อมูลที่จำเป็น'])
                ->setStatusCode(400);
        }
        $duplicate = $this->topic
            ->where('int_saksiam_topic_nameTH', $topicnameTH)
            ->orWhere('int_saksiam_topic_nameEN', $topicnameEN)
            ->first();
        if ($duplicate) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'ชื่อหัวข้อแบบสอบถามนี้มีอยู่ในระบบแล้ว'
            ])->setStatusCode(400);
        }
        try {
            $topicData = [
                'int_saksiam_topic_nameTH' => $topicnameTH,
                'int_saksiam_topic_nameEN' => $topicnameEN,
                'int_saksiam_topic_savename' => $topicnsavename,
                'int_saksiam_topic_active' => $topicactive,
            ];

            $topicID = $this->topic->createTopicData($topicData);
            return $this->response->setJSON([
                'message' => 'Folder created successfully',
                'int_saksiam_topic_id' => $topicID,
                'topic_data' => $topicData
            ])->setStatusCode(200);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'error' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    public function showTopicDataID($topicID = null)
    {
        if (is_null($topicID)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Topioc ID is required'
            ])->setStatusCode(400);
        }
        try {
            $folder = $this->topic->showTopicID($topicID);
            return $this->response->setJSON([
                'status' => 200,
                'data' => $folder,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => $e->getMessage()])
                ->setStatusCode(500);
        }
    }

    public function updateTopicDetail($topicID = null)
    {
        if (is_null($topicID)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Topioc ID  is required'
            ])->setStatusCode(400);
        }

        try {
            $data = $this->request->getJSON(true);
            if (empty($data)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'No data received'
                ])->setStatusCode(400);
            }

            $folderEventData = $this->topic->find($topicID);
            if (!$folderEventData) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Topic data not found'
                ])->setStatusCode(404);
            }

            $updateDataTopic = [];
            /* ===================== CHECK DUPLICATE ===================== */
            if (isset($data['nameTH']) || isset($data['nameEN'])) {

                $nameTH = $data['nameTH'] ?? null;
                $nameEN = $data['nameEN'] ?? null;

                $duplicate = $this->topic
                    ->groupStart()
                    ->where('int_saksiam_topic_nameTH', $nameTH)
                    ->orWhere('int_saksiam_topic_nameEN', $nameEN)
                    ->groupEnd()
                    ->first();

                if ($duplicate) {
                    return $this->response->setJSON([
                        'status' => false,
                        'message' => 'ชื่อหัวข้อแบบสอบถามนี้มีอยู่ในระบบแล้ว'
                    ])->setStatusCode(400);
                }
            }
            /* =========================================================== */

            if (isset($data['nameTH']) && $data['nameTH'] !== $folderEventData['int_saksiam_topic_nameTH']) {
                $updateDataTopic['int_saksiam_topic_nameTH'] = $data['nameTH'];
            }
            if (isset($data['nameEN']) && $data['nameEN'] !== $folderEventData['int_saksiam_topic_nameEN']) {
                $updateDataTopic['int_saksiam_topic_nameEN'] = $data['nameEN'];
            }
            if (isset($data['active']) && $data['active'] !== $folderEventData['int_saksiam_topic_active']) {
                $updateDataTopic['int_saksiam_topic_active'] = $data['active'];
            }
            if (isset($data['updatename']) && $data['updatename'] !== $folderEventData['int_saksiam_topic_updatename']) {
                $updateDataTopic['int_saksiam_topic_updatename'] = $data['updatename'];
            }
            if (isset($data['changename']) && $data['changename'] !== $folderEventData['int_saksiam_topic_changename']) {
                $updateDataTopic['int_saksiam_topic_changename'] = $data['changename'];
            }

            if (!empty($updateDataTopic)) {
                $this->topic->updateData($topicID, $updateDataTopic);
            }
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Topic details updated successfully'
            ])->setStatusCode(200);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    public function deleteTopicData($topicID)
    {
        try {
            if (empty($topicID) || !is_numeric($topicID)) {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Invalid Vedio ID'
                ])->setStatusCode(400);
            }
            if ($this->topic->delete($topicID)) {
                return $this->response->setJSON([
                    'status' => true,
                    'message' => 'Event deleted successfully'
                ])->setStatusCode(200);
            } else {
                return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Failed to delete event'
                ])->setStatusCode(500);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'An unexpected error occurred'
            ])->setStatusCode(500);
        }
    }

    public function getTopicDataMove()
    {
        $showTopic = $this->topic->getTopicMove();
        if ($showTopic) {
            return $this->response->setJSON([
                'status' => true,
                'message' => 'Data topic retrieved successfully',
                'result' => $showTopic
            ]);
        } else {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Data not found'
            ])->setStatusCode(404);
        }

    }

    public function updateTopicdataMove()
    {
        $data = $this->request->getJSON(true);
        if (empty($data)) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'No data received'
            ])->setStatusCode(400);
        }
        if (empty($data['newOrder']) || !is_array($data['newOrder'])) {
            return $this->response->setJSON([
                'status' => false,
                'message' => 'Invalid or missing data'
            ])->setStatusCode(code: 400);
        }
        $this->topic->updateTopicMove($data['newOrder']);

        return $this->response->setJSON([
            'status' => true,
            'message' => 'List Vedio order updated successfully'
        ])->setStatusCode(200);
    }

    public function listgetTopiceData()
    {
        $showdata = $this->topic->getTopicWebsite();
        return $this->response->setJSON([
            'status' => true,
            'message' => 'Data topic retrieved successfully',
            'data' => $showdata,
        ]);
    }
}

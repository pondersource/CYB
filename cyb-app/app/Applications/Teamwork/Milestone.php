<?php

namespace App\Applications\Teamwork;

class Milestone extends Model
{
    protected function init()
    {
        // this is the list of fields that can send the api
        $this->fields = [
            'title'       => true,
            'description' => false,
            'deadline'    => [
                'required'=>true,
                'attributes'=>[
                    'type'=>'integer'
                ]
            ],//format YYYYMMDD
            'notify'      => [
                'required'=>false,
                'attributes'=>[
                    'type'=>'boolean'
                ]
            ],
            'reminder'=>[
                'required'=>false,
                'attributes'=>[
                    'type'=>'boolean'
                ]
            ],
            'private'=>[
                'required'=>false,
                'attributes'=>[
                    'type'=>'boolean'
                ]
            ],
            'responsible_party_ids' => true,
            # USE ONLY FOR UPDATE OR PUT METHOD
            'move_upcoming_milestones'=>[
              'sibling'=>true,
              'required'=>false,
              'attributes'=>['type'=>'boolean']
            ],
            'move_upcoming_milestones_off_weekends'=>[
              'sibling'=>true,
              'required'=>false,
              'attributes'=>['type'=>'boolean']
            ]
        ];
    }

    /**
     * Complete
     *
     * PUT /milestones/#{id}/complete.xml
     *
     * Marks the specified milestone as complete. Returns Status 200 OK.
     *
     * @param int $id
     *
     * @return bool
     * @throws App\Applications\Teamwork\Exception
     */
    public function complete($id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            throw new Exception('Invalid param id');
        }
        return $this->rest->put("$this->action/$id/complete");
    }

    /**
     * Uncomplete
     *
     * PUT /milestones/#{id}/uncomplete.xml
     *
     * Marks the specified milestone as uncomplete. Returns Status 200 OK.
     *
     * @param int $id
     *
     * @return bool
     * @throws \App\Applications\Teamwork\Exception
     */
    public function uncomplete($id)
    {
        $id = (int) $id;
        if ($id <= 0) {
            throw new Exception('Invalid param id');
        }
        return $this->rest->put("$this->action/$id/uncomplete");
    }

    /**
     * Get all milestone
     *
     * @param string $filter
     *
     * @return @return \App\Applications\Teamwork\Response\Model
     * @throws @return \App\Applications\Teamwork\Exception
     */
    public function getAll($filter = 'all')
    {
        return $this->rest->get("$this->action", $this->getParams($filter));
    }

    /**
     * Get all milestone
     *
     * @param $project_id
     * @param string $filter
     *
     * @return @return \App\Applications\Teamwork\Response\Model
     * @throws @return \App\Applications\Teamwork\Exception
     */
    public function getByProject($project_id, $filter = 'all')
    {
        $project_id = (int) $project_id;
        if ($project_id <= 0) {
            throw new Exception('Invalid param project_id');
        }
        return $this->rest->get(
            "projects/$project_id/$this->action",
            $this->getParams($filter)
        );
    }

    /**
     * @param $filter
     *
     * @return array
     * @throws \App\Applications\Teamwork\Exception
     */
    private function getParams($filter)
    {
        $params = [];
        if ($filter) {
            $filter = (string) $filter;
            $filter = strtolower($filter);
            if ($filter !== 'all') {
                $validate = ['completed', 'incomplete', 'late', 'upcoming'];
                if (!in_array($filter, $validate)) {
                    throw new Exception('Invalid value for param filter');
                }
                $params['find'] = $filter;
            }
        }
        return $params;
    }

    /**
     * @param array $data
     *
     * @return int
     * @throws \App\Applications\Teamwork\Exception
     */
    public function insert(array $data)
    {
        $project_id = empty($data['project_id']) ? 0: $data['project_id'];
        if ($project_id <= 0) {
            throw new Exception('Required field project_id');
        }
        return $this->rest->post("projects/$project_id/$this->action", $data);
    }
}
<?php

namespace App\Applications\Teamwork;

class People extends Model {

     protected function init()
    {
        $this->fields = [
            'first_name' => true,
            'last_name' => true,
            'email_address'=>true,
            'user_name' => true,
            'password' => false,
            'company_id' => false,
            'title' => false,
            'phone_number_mobile'=>false,
            'phone_number_office'=>false,
            'phone_number_office_ext'=>false,
            'phone_number_fax'=>false,
            'phone_number_home'=>false,
            'im_handle'=>false,
            'im_service'=>[
                'required'=>false,
                'validate'=>[
                    'GTalk',
                    'AOL',
                    'ICQ',
                    'MSN',
                    'Jabber',
                    'Yahoo',
                    'Skype',
                    'Twitter'
                ]
            ],
            'date_format'=>[
                'required'=>false,
                'validate'=>[
                    'dd.mm.yyyy',
                    'dd/mm/yyyy',
                    'mm.dd.yyyy',
                    'mm/dd/yyyy',
                    'yyyy-mm-dd',
                    'yyyy.mm.dd'
                ]
            ],
            'send_welcome_email'=>[
                'required'=>false,
                'type'=>'boolean'
            ],
            'receive_daily_reports'=>[
                'required'=>false,
                'type'=>'boolean'
            ],
            'welcome_email_message'=>false,
            'auto_give_project_access'=>[
                'required'=>false,
                'type'=>'boolean'
            ],
            'open_id'=>false,
            'notes'=>[
                'required'=>false,
                'type'=>'boolean'
            ],
            'user_language'=>[
                'required'=>false,
                'validate'=>[
                    'EN',
                    'FR',
                    'AR',
                    'BG',
                    'ZH',
                    'HR',
                    'CS',
                    'DA',
                    'NL',
                    'FI',
                    'DE',
                    'EL',
                    'HU',
                    'ID',
                    'IT',
                    'JA',
                    'KO',
                    'NO',
                    'PL',
                    'PT',
                    'RO',
                    'RU',
                    'ES',
                    'SV'
                ]
            ],
            'administrator'=>false,
            'can_add_projects'=>[
                'required'=>false,
                'type'=>'boolean'
            ]
        ];
        $this->parent = 'person';
        $this->action = 'people';
    }

    /**
     * @param id
     * @param null project id
     * @return \App\Applications\Teamwork\Response\Model
     * @return \App\Applications\Teamwork\Exception
     */
    public function get($id, $project_id = null) {
        $id = (int) $id;
        if($id <= 0) throw new Exception("Invalid param id");

        $project_id = (int) $project_id;
        $action = "$this->action/$id";
        if($project_id) {
            $action = "projects/$project_id/$action";
        }    
        return $this->rest->get($action);
    }

    /**
     * get people
     * GET /people
     * All user will be return including the user
     * @param $pageSize int
     * @param $size int
     * @return \App\Applications\Teamwork\Response\Model
     * @return \App\Applications\Teamwork\Exception
     */
    public function getAll($pageSize = 200, $page = 1) {
        return $this->rest->get($this->action, [
            'pageSize' => $pageSize,
            'page' => $page
        ]);
    }

    /**
     * Get all people within project
     * GET /projects/{project_id}/people
     * @param int id
     * @return \App\Applications\Teamwork\Response\Model
     * @return \App\Applications\Teamwork\Exception
     */
    public function getByProject($id) {
        $id = (int) $id;
        return $this->rest->get("projects/$id/$this->action");    
    }

    /**
     * Get people with in a company
     *  GET /companies/#{company_id}/people
     * Retreives the details for all the people from the submitted company
     * @param  int $id
     * @return \App\Applications\Teamwork\Response\Model
     * @return \App\Applications\Teamwork\Exception
     */
    public function getByCompany($id) {
        $id = (int) $id;
        return $this->rest->get("companies/$id/$this->action");
    }

    /**
     * Get user by email
     * GET /people
     * Retreive user by email
     * @param string $email_address
     * @return \App\Applications\Teamwork\Response\Model
     * @return \App\Applications\Teamwork\Exception
     */
    public function getByEmail($email_address) {
        $email_address = (string) $email_address;
        return $this->rest->get($this->action, [
            'email_address' => $email_address,
        ]);
    }

    /**
     * Add new user 
     * POST /people
     * @param array $data
     * @return int
     * @return \App\Applications\Teamwork\Exception
     */
    public function insert(array $data)
    {
        //validate email address

        if (!empty($data['email_address']) &&
                !filter_var($data['email_address'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception(
                'Invalid value for field email_address'
            );
        }
        $project_id = empty($data["project_id"]) ? 0 : $data["project_id"];
        $permissions = empty($data["permissions"]) ? null : (array) $data["permissions"];

        unset($data["project_id"], $data["permissions"]);

        $id = parent::insert($data);

        //add permission to project
        if($project_id) {
            $permission = Factory::build("project/people");
            $permission->add($project_id, $id);

            if($permissions) {
                $permissions["person_id"] = $id;
                $permissions["project_id"] = $project_id;
                $permission->update($permissions);
            }
        }
    }

    /**
     * @param array $data
     * @return bool
     * @return \App\Applications\Teamwork\Exception
     */
    public function update(array $data)
    {
        // validate email address
        if (!empty($data['email_address']) &&
                !filter_var($data['email_address'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception(
                'Invalid value for field email_address'
            );
        }
    
        $project_id = empty($data['project_id']) ? 0 : $data['project_id'];
        $permissions = empty($data['permissions']) ? null :
                                                (array) $data['permissions'];
        unset($data['project_id'], $data['permissions']);
        $save = false;
        
        if(!empty($save)) {
          $save = parent::update($data);
        }

        if($project_id) {
            $permission = Factory::build("project/people");

            try {
                $add = $permission->add($project_id, $data["id"]);
            } catch(Exception $e) {
              $add = $e->getMessage() === 'User is already on project';
            }
            $save = $save && $add;

            if($add && $permissions) {
                $permissions["person_id"] = $data["id"];
                $permissions["project_id"] = $project_id;
                $save = $permission->update($permissions);
            }
        }
        return $save;
    }

    /**
     * @param int $id
     * @param null $project_id
     * @return bool
     * @return \App\Applications\Teamwork\Exception
     */
    public function delete($id, $project_id = null) {
        $id = (int) $id;

        if($id <= 0) throw new Exception("Invalid param id");

        $project_id = (int) $project_id;

        $action = "$this->action/$id";
        if($project_id) {
            $action = "projects/$project_id/$action";
        }

        return $this->rest->delete($action);
    }
}
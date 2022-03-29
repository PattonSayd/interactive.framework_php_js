<?php 

namespace core\base\model;

use core\base\controller\Methods;
use core\base\controller\Singleton;
use core\base\exception\AuthException;

class UserModel extends Model
{
    use Singleton; 

    use Methods;

    private $cookie_name = 'identifier';

    private $cookie_admin_name = 'identifier';

    private $user_data = [];

    private $error = [];

    private $user_table = 'visitors';

    private $admin_table = 'users';

    private $blocked_table = 'blocked_access';


    public function getAdminTable()
    {
       return $this->admint_table;
    }

    public function getBlockedTable()
    {
       return $this->blocked_table;
    }

    public function getLastError()
    {
       return $this->error;
    }

    public function setAdmin()
    {
       $this->cookie_name = $this->cookie_admin_name;

       $this->user_table = $this->admin_table;

        if(!in_array($this->user_table, $this->getTables())){

            $query = 'create table ' . $this->userTable . `
            (
                id int(11) auto_incriment primary key,
                name varchar(255) null,
                login varchar(255) null,
                password varchar(32) null,
                credentials text null
                created_at timestamp default current_timestamp
            )
                charset = utf8
            `;

            if(!$this->query($query, 'u')){

                exit('Ошибка создания таблицы ' . $this->user_table);
            }

            $this->add($this->user_table, [

                'fields' => ['name' => 'name', 'login' => 'admin', 'password' => md5('sayd')],
            ]);
         }

       if(!in_array($this->blocked_table, $this->getTables())){
           
            $query = 'create table ' . $this->userTable . `
            (
                id int(11) auto_incriment primary key,
                login varchar(255) null,
                ip varchar(32) null,
                trying tinyint(1) null
                created_at timestamp default current_timestamp
            )
                charset = utf8
            `;       // time datatime null 

            if(!$this->query($query, 'u')){

                exit('Ошибка создания таблицы ' . $this->user_table);
            }
        }   
    }

    public function checkUser($id = false, $admin = false)
    {
        $admin && $this->user_table !== $this->admin_table && $this->setAdmin();

        $method = 'unPackage';

        if($id){

            $this->user_data['id'] = $id;

            $method = 'set';
        }

        try{

            $this->$method();
            
        }catch(AuthException $e){
            
            $this->error = $e->getMessage();

            !empty($e->getCode()) && $this->writeLog($this->error, 'log_user.txt');
            
            return false;
        }

        return $this->user_data;
    }

}
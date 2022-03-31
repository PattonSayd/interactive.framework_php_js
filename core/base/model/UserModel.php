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

    private $cookie_admin_name = 'GNEngineCache';

    private $user_data = [];

    private $error = [];

    private $user_table = 'visitors';

    private $admin_table = 'admin';

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

            $query = 'create table ' . $this->user_table . '
            (
                id int(11) auto_increment primary key,
                name varchar(255) null,
                login varchar(255) null,
                password varchar(32) null,
                credentials text null,
                created_at timestamp default current_timestamp
            )
                charset = utf8
            ';

            if(!$this->query($query, 'u')){

                exit('Ошибка создания таблицы ' . $this->user_table);
            }

            $this->add($this->user_table, [

                'fields' => ['name' => 'name', 'login' => 'admin', 'password' => md5('sayd')],
            ]);
         }

       if(!in_array($this->blocked_table, $this->getTables())){
           
            $query = 'create table ' . $this->blocked_table . '
            (
                id int(11) auto_increment primary key,
                login varchar(255) null,  
                ip varchar(32) null,
                trying tinyint(1) null,
                created_at timestamp default current_timestamp
            )
                charset = utf8
            ';       // time datatime null 

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


    private function set()
    {
        $coockie_string = $this->packet();

        if($coockie_string){

            setcookie($this->coockie_name, $coockie_string, time() + 60*60*24*365*10, PATH_SEPARATOR);

            return true;
        }

        throw new AuthException('Ошибка формирования coockie', 1);
    }


    private function packet()
    {
        if(!empty($this->user_data['id'])){

            $data['id'] = $this->user_data;

            $data['version'] = COOKIE_VERSION;

            $data['coockieTime'] = date('Y-m-d H:i:s');

            return Crypt::instance()->encrypt(json_encode($data));
        }

        throw new AuthException('Некорректный идентификатор пользователя - ' . $this->user_data['id'], 1);
    }


    private function unPackage()
    {
        if(empty($_COOKIE[$this->cookie_name])){

            throw new AuthException('Отсутствует coockie пользователя');
        }

        $data = json_decode(Crypt::instance()->decrypt($_COOKIE[$this->cookie_name]), true);

        if(empty($data['id']) || empty($data['version']) || empty($data['coockieTime'])){

            $this->logout();

            throw new AuthException('Некорректные данные в coockie пользователя', 1);
        }

        $this->validate($data);     
        
        $this->userData = $this->select($this->user_table, [

            'where' => ['id' => $data['id']],
        ]);

        if(!$this->user_data) {

            $this->logout();

            throw new AuthException('Не найдены данные в таблице ' . $this->user_table . 'по идентификатору' . $data['id'], 1);
        }

        $this->user_table = $this->user_data[0];
        
        return true;
    }


    private function validate($data)
    {
        if(!empty(COOKIE_VERSION)){
            
            if($data['version'] !== COOKIE_VERSION){

                $this->logout();

                throw new AuthException('Некорректные версия coockie');
            }
        }

        if(!empty(COOKIE_TIME)){

            if((new \DateTime()) > (new \DateTime($data['cookie_time'])) ->modify(COOKIE_TIME . 'minute')){

                throw new AuthException('Перевышено время бездействия пользователя');              
            }
            
        }
    }


    public function logout()
    {
        setcookie($this->cookie_name, '', 1, PATH);
    }

}
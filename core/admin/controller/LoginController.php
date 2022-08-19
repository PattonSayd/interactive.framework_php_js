<?php 

namespace core\admin\controller;

use core\base\controller\Controller;
use core\base\model\AuthModel;
use core\base\settings\Settings;

class LoginController extends Controller
{
    protected $model;
    
    protected function inputData()
    {

        $this->model = AuthModel::instance();

        $this->model->setAdmin();

        if(isset($this->parameters['logout'])){

            $this->checkAuth(true);

            $user_log = 'Выход пользователя ' . $this->userId['name'];

            $this->writeLog($user_log, 'user_log.txt', 'Access user');

            $this->model->logout();

            $this->redirect(PATH);
        } 

        if($this->isPost()){

            if(empty($_POST['token']) || $_POST['token'] !== $_SESSION['token']){

                exit('Cookie error');
                
            }

            $time_clean = (new \DateTime())->modify('-' . BLOCK_TIME . ' hour')->format('Y-m-d H:i:s');

            $this->model->delete($this->model->getBlockedTable(), [
                'where' => ['time' => $time_clean],
                'operand' => ['<']
            ]);


            $ipUser = filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP) ?:
                        (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP) ?: @$_SERVER['REMOTE_ADDR']); 
            
            $trying = $this->model->select($this->model->getBlockedTable(), [
                'fields' => ['trying'],
                'where' => ['ip' => $ipUser],
            ]);

            $trying = !empty($trying) ? $this->clearNum($trying[0]['trying']) : 0;

            $success = 0;

            if(!empty($_POST['login']) && !empty($_POST['password']) && $trying < 3){

                $login = $this->clearStr($_POST['login']);

                $password = md5($this->clearStr($_POST['password']));

                $user_data = $this->model->select($this->model->getAdminTable(), [
                    'field' => ['id', 'name'],
                    'where' => ['login' => $login, 'password' => $password]
                ]);

                if(!$user_data){

                    $method = 'add';

                    $where = [];

                    if($trying){

                        $method = 'edit';

                        $where['ip'] = $ipUser; 
                    }
                    
                    $this->model->$method($this->model->getBlockedTable(), [
                        'fields' => ['login' => $login, 'ip' => $ipUser, 'time' => 'NOW()', 'trying' => ++$trying],
                        'where' => $where
                    ]);

                    $error = 'Неверное имя пользователя или пароль - ' . $ipUser . ', логин - ' . $login;    

                }else{

                    if(!$this->model->checkUser($user_data[0]['id'])){

                        $error = $this->model->getLastError();
                        
                    }else{

                        $error = 'Вход пользователя - ' . $login;

                        $success = 1;                            
                    }
                    
                }
                
            }elseif($trying >= 3){

                $this->model->logout();

                $error = 'Перевышено максимальное количество попыток ввода пароля - ' . $ipUser;
                
            }else{

                $error = 'Заполните обязательные поля';
                
            }

            $_SESSION['res']['answer'] = $success ? '<div class="gn-item gn-before gn-success">
                                                        <span><i class="gn-icon gn-success-color icon-checkmark-circle"></i></span>
                                                        <span class="gn-msg gn-success-color"><b>Welcome! </b>'. $user_data[0]['name'] .'</span> 
                                                        <span class="gn-btn-close">
                                                        <span class="gn-close gn-success-color-hover"><i class="gn-close-icon gn-success-color icon-cross"></i></span>
                                                        </span>
                                                        </div>' : preg_split('/\s*\-/', $error, 2, PREG_SPLIT_NO_EMPTY)[0];

            $this->writeLog($error, 'user_log.txt', 'Access user');

            $path = null;

            $success && $path = PATH . Settings::get('routes')['admin']['alias'];

            $this->redirect($path);
        }


        return $this->render('', ['admin_path' => Settings::get('routes')['admin']['alias']]);
        
    }
    
}
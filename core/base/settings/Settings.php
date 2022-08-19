<?php

namespace core\base\settings;

use core\base\controller\Singleton;

class Settings
{
    use Singleton;

    # put a slash(/) at the end of the PATH
    private $routes = [
        'admin' => [
            'alias'  => 'admin',
            'path'   => 'core/admin/controller/',
            'hrUrl'  => false,
            'routes' => [
                 'dash' => 'info' # url .../dash   connected InfoController
            ]
        ],
        'settings' => [
            'path' => 'core/base/settings/'
        ],
        'plugins'   => [
            'path'  => 'core/plugins/',
            'hrUrl' => false,
            'dir'   => ''
        ],
        'user' =>[
            'path'   => 'core/user/controller/',
            'hrUrl'  => true,
            'routes' => [
                'hello' => 'info/page/first', # url = hello, controller = InfoController, inputMethod = hello, outputMethod = first
            ]
        ],
        'default' => [
            'controller'   => 'IndexController',
            'inputMethod'  => 'inputData',
            'outputMethod' => 'outputData'
        ]
    ];
    
    private $defaultTable = 'admin';

    private $extension = 'core/admin/extension/';

    private $formTemplatesPath = 'core/admin/view/include/form/';

    private $messages = 'core/base/messages/';


    private $templates = [
       # template => rows DB    
        'input'    => ['name', 'login', 'password'],
        'select'   => ['parent_id', 'menu_position'],
        'radio'    => ['visible', 'show_top_menu', 'credentials'],
        'checkbox' => ['pages'],
        'keywords' => ['keywords'],
        'image'    => ['image'],
        'gallery'  => ['gallery'],
        'textarea' => ['description'],

    ];

    private $fileTemplates = ['image', 'gallery'];

    private $rootTable = ['admin'];

    private $projectTable = [
        'admin'          => ['name' => 'Users', 'icon' => 'icon-user'],
        'blocked_access' => ['name' => 'Blocked', 'icon' => 'icon-user'],
    ];

    private $blocks = [
        'left' => [],
        'right' => ['image', 'gallery'],
        'center' => ['content'],
    ];

    private $translate = [
        'name'          => ['Name', 'No more than 50 characters'],
        'password'      => ['Password', 'No more than 8 symbols'],
        'login'         => ['Login'],
        'content'       => ['Описание', 'Не менее 1000 символов'],
        'visible'       => ['Visibility', 'Default: show'],
        'keywords'      => ['Keywords'],
        'menu_position' => ['Позиция меню'],
        'parent_id'     => ['Родительская позиция'],
        'image'         => ['Picture'],
        'description'   => ['SEO description'],
        'gallery'       => ['Gallery'],
        'phone'         => ['Phone'],
        'email'         => ['Email'],
        'address'       => ['Address'],
        'alias'         => ['URLs'],
        'show_top_menu' => ['Show top menu'],
        'credentials'   => ['Credentials'],
    ];

    private $root = [
        'name'   => 'root',
        'tables' => [],
    ];

    private $radio = [
        'visible' => [1 => 'show', 0 => 'hide', 'default' => 'show'],
        'show_top_menu' => [1 => 'show', 0 => 'hide', 'default' => 'show']
    ];
    
    private $validation = [
        'name'     => ['empty' => true, 'trim' => true],
        'price'    => ['int' => true],
        'login'    => ['empty' => true, 'trim' => true],
        'password' => ['crypt' => true, 'empty' => true],
        'keywords' => ['count' => 70, 'trim' => true],
        'content'  => ['trim' => true],
    ];

    private $manyToMany = [
        'comments_pages' => ['comments', 'pages'] # 3 ячейка: 'type' => 'child' || 'root' || 'all' ИЛИ ПУСТОТА;
    ];

    
 
    public static function get($property)
    {
        return self::instance()->$property;  
    }  

    public function clueProperties($class)
    {
        $properties = [];

        foreach ($this as $name => $item) {
            $property = $class::get($name);
            $properties[$name] = $property;

            if (is_array($item) && is_array($property)) {
                $properties[$name] = $this->arrayMergeRecursive($this->$name , $property);
                continue;
            }

            if(!$property){
                $properties[$name] = $this->$name;
            }
        }
        return  $properties;
    }    

    public function arrayMergeRecursive(){
        $arrays = func_get_args();  
#          0:                 1:
        $base = array_shift($arrays);

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if (is_array($value) && is_array($base[$key]))
                    $base[$key] = $this->arrayMergeRecursive($base[$key], $value);
                else {
                    if(is_int($key)) {
                        if (!in_array($value, $base)) 
                            array_push($base, $value);

                        continue;
                    }
                    $base[$key] = $value;
                }
            }
        }
        return $base;
    }
}
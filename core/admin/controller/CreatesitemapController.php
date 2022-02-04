<?php 
namespace core\admin\controller;

use core\admin\controller\AdminController;
use core\base\controller\Methods;
use DateTime;

class CreatesitemapController extends AdminController
{
    use Methods;

    /** Свойства парсинга
     * 
     * @param array/массив ссылок
     */
    protected $all_links = [];

    /** 
     * @param array/временные элементы для парсинга запросов
     */
    protected $temp_links = [];

    /** 
     * @param array/массив 404
     */
    protected $bad_links = [];

    /** 
     * @param int/максимальное количество допутимых ссылок
     */
    protected $max_links = 5000;

    /**
     * @param string/имя файла для логирования ошибок    
     */
    protected $parsingLogFile = 'parsing_log.txt';

    /**
     * @param array/расщирения файлов    
     */
    protected $file_array = ['jpg', 'png', 'jpeg', 'xls', 'xlsx', 'pdf', 'mp3', 'mp3'];

    /**
     * @param array/фильтрация   
     */
    protected $filter_array = [
        'url' => [],   //- исключаемые ссылки 
        'get' => []
    ];

# -------------------- INPUT DATA ------------------------------------------------ 

    public function inputData($links_count = 1, $redirect = true)
    {   
        if(!function_exists('curl_init'))
            $this->cancel(0, 'Library CURL absent. Creation of site map imposable' , $log_message = '', true);

        if(!$this->userId) $this->parent_inputData();

        if(!$this->checkParsingTable())
            $this->cancel(0, 'You have problem with database table parsing_data', $log_message = '', true);

        set_time_limit(0);

        $reserve = $this->model->select('parsing_data')[0]; 

        $table_rows = [];
        
        foreach($reserve as $name => $item){
            $table_rows[$name] = '';
            
            if($item){
                $this->$name = json_decode($item);  # all_links OR temp_links
            }elseif($name === 'all_links' || $name === 'temp_links' ){
                $this->$name = [SITE_URL];
            }
        }

        $this->max_links = (int)$links_count > 1 ? ceil($this->max_links / $links_count) : $this->max_links;

        while($this->temp_links){
            $temp_links_count = count($this->temp_links);

            $links = $this->temp_links;

            $this->temp_links = [];

            if($temp_links_count > $this->max_links){
                $links = array_chunk($links, ceil($temp_links_count / $this->max_links));

                $count_chunks = count($links);

                for($i = 0; $i < $count_chunks; $i++){
                    $this->parsing($links[$i]);

                    unset($links[$i]);

                    if($links){
                        foreach($table_rows as $name => $item){

                            if($name === 'temp_links') $table_rows[$name] =  json_encode(array_merge(...$links));
                            else $table_rows[$name] = json_encode($this->$name);
                        }

                        $this->model->edit('parsing_data', [
                            'fields' => $table_rows
                        ]);
                    }
                }   

            }else{
                $this->parsing($links);
            }

            foreach ($table_rows as $name => $item) {
                $table_rows[$name] = json_encode($this->$name);
            }

            $this->model->edit('parsing_data', [
                'fields' => $table_rows
            ]);
        }
        
        $this->parsing(SITE_URL);
            
        $this->createSitemap();

        !$_SESSION['res']['answer'] && $_SESSION['res']['answer'] = '<div class="alert alert-success alert-styled-left alert-arrow-left alert-dismissible alert-setInterval">
                                                            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                                            <span class="font-weight-semibold">Well done!</span> Sitemap is crealed.</div>';

        $this->redirect();
    }

# -------------------- PARSING ---------------------------------------------------

    protected function parsing($urls, $index = 0)
    {    
        if(!$urls) return;

        $curlMulty = curl_multi_init();

        $curl = [];

       foreach($urls as $i => $url){

            $curl[$i] = curl_init(); # инициализация библиотеки 'cURL' 

            curl_setopt($curl[$i], CURLOPT_URL, $url); # загружаемый URL 
            curl_setopt($curl[$i], CURLOPT_RETURNTRANSFER, true); # возврат результата
            curl_setopt($curl[$i], CURLOPT_HEADER, true); # включения заголовков в выводов 
            curl_setopt($curl[$i], CURLOPT_FOLLOWLOCATION, 1); # следования любому заголовку "Location: " 
            curl_setopt($curl[$i], CURLOPT_TIMEOUT, 120); # максимально позволенное количество секунд для выполнения cURL-функции 
            curl_setopt($curl[$i], CURLOPT_ENCODING, 'gzip,deflate'); # позволяет декодировать запрос

            curl_multi_add_handle($curlMulty, $curl[$i]); # добавление дескрипторов(cURL_MULTI, cURL)
        }

        do{
            $status = curl_multi_exec($curlMulty, $active); # запускает подсоединения текущего дескриптора cURL
            $info = curl_multi_info_read($curlMulty); # возвращает информацию о текущих операциях
          
            if(false !== $info){  

                if($info['result'] !== 0){

                    $i = array_search($info['handle'], $curl);
                    
                    $error = curl_errno($curl[$i]);
                    $message = curl_error($curl[$i]);
                    $header = curl_getinfo($curl[$i]);

                    if($error != 0){

                        $this->cancel(0, 'Error loading ' . $header['url'] . ' http code: ' . $header['http_code']  . ' error: ' . $error . ' message ' . $message);
                    }
                }
            }

            if($status > 0){

                $this->cancel(0, curl_multi_strerror($status));
            }
            
        }while($status === CURLM_CALL_MULTI_PERFORM || $active); # если CURLM_CALL_MULTI_PERFORM не будет работать то: $status > $active 

        if(!preg_match('/Content-Type:\s+text\/html/ui', $out)){
            unset($this->all_links[$index]);

            $this->all_links = array_values($this->all_links);

            return;
        }

        if(!preg_match('/HTTP\/\d\.?\d?\s+20\d/ui', $out)){
            $this->writeLog('Некорректная директория сайта - ' . $urls, $this->parsingLogFile);

            unset($this->all_links[$index]);

            $this->all_links = array_values($this->all_links);

            return;
        }

        preg_match_all('/<a\s*?[^>]*?href\s*?=(["\'])(.+?)\1[^>]*?>/ui', $out, $links);

        if ($links[2]) {

            foreach ($links[2] as $link) {

                # если в 'url' присутствует конечный слэш
                if ($link === '/' || $link === SITE_URL . '/') {
                    continue;
                }

                foreach ($this->file_array as $ext) {
                    if ($ext) {
                        $ext = addslashes($ext);
                        $ext = str_replace('.', '\.', $ext);

                        if (preg_match('/' . $ext . '(\s*?$|\?[^\/]*$)/ui', $link)) {
                            continue 2;
                        }
                    }
                }

                if (strpos($link, '/') === 0) {
                    $link = SITE_URL . $link;
                }

                $site_url = mb_str_replace('.', '\.', mb_str_replace('/', '\/', SITE_URL));

                if (!in_array($link, $this->bad_links) 
                        && !preg_match('/^(' . $site_url . ')?\/?#[^\/]*?$/ui', $link)
                        && strpos($link, SITE_URL) === 0
                        && !in_array($link, $this->all_links)) {

                    if($this->filter($link)){


                        // $this->temp_links[] = $link;
                        $this->all_links[] = $link;

                        $this->parsing($link, count($this->all_links));
                    }
                }
            }
        } 

    }

# -------------------- CREATE LINKS ----------------------------------------------

    protected function createLinks($content)
    {
        
    }

# -------------------- FILTER ----------------------------------------------------

    protected function filter($link)
    {
        if($this->filter_array){

            foreach($this->filter_array as $type => $values){

                if($values){

                    foreach($values as $item){
                        
                        $item = str_replace('/', '\/', addslashes($item));

                        if($type === 'url'){

                            if(preg_match('/^[^\?]*' . $item . '/ui',  $link)){   // +++++++++++++++++++++
                                return false;
                            }
                        } 

                        if($type === 'get'){
                                        # '/id?order=ASC&name=Jones$amp;secondname=Devi'
                            if(preg_match('/(\?|&amp;|=|&)' .$item . '(=|&amp;|&|$)/ui',  $link)){
                                return false;
                            }
                        } 
                    }
                }

            }
        }

        return true;
    }
    
# -------------------- CANCEL ----------------------------------------------------
    
    protected function cancel($success = 0, $message = '', $log_message = '', $exit = false)
    {
        $alert = [];

        $alert['success'] = $success;
        $alert['message'] = $message ? $message : 'ERROR PARSING';
        $log_message = $log_message ? $log_message : $alert['message'];
        $class = 'success';

        if(!$alert['success']){
            $class = 'error';
            $this->writeLog($log_message, 'parsing_log.txt');
        }

        if($exit){
            $alert['message'] = '<div class="' . $class . '">' . $alert['message'] . '</div>' ;

            exit(json_encode($alert));
        }
    }

# -------------------- CHECK PARSING TABLE ---------------------------------------  

    protected function checkParsingTable()
    {
        $tables = $this->model->getTables();

        if(!in_array('parsing_data', $tables)){
            
            $query = "CREATE TABLE parsing_data (all_links longtext, temp_links longtext, bad_links longtext)";

             if(!$this->model->query($query, 'c') ||
                !$this->model->add('parsing_data',
                     ['fields' => ['all_links' => '', 'temp_links' => '', 'bad_links' => '']])){
                    return false;
             }
        }
        return true;
    }
    
    
# -------------------- CREATE SITE MAP -------------------------------------------

    protected function createSitemap()
    {
        

    }
}
//'<div class="alert alert-info alert-styled-left alert-dismissible">
// <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
// <span class="font-weight-semibold">Warning! </span>Library CURL apsent. Creation of sitemap imposible.</span></div>'; 
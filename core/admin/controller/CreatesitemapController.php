<?php 
namespace core\admin\controller;

use core\admin\controller\AdminController;
use core\base\controller\Methods;
use core\base\settings\Settings;

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
    protected $max_links = 200;

    /**
     * @param string/имя файла для логирования ошибок    
     */
    protected $parsingLogFile = 'parsing_log.txt';

    /**
     * @param array/расщирения файлов    
     */
    protected $file_array = [];

    /**
     * @param array/фильтрация   
     */
    protected $filter_array = [
        'url' => ['order'],   //- исключаемые ссылки 
        'get' => []
    ];

# -------------------- INPUT DATA ------------------------------------------------ 

    protected function inputData($links_count = 1, $redirect = true)
    {   
        $links_count = $this->clearNum($links_count);
        
        # -----------------------------------------------
        # если не зарегистрирована функция "cURL_INIT", 
        # свяжитесь с поставщиками веб-серверов
        # для установки библиотеки
        # "CURL_INIT" для вашего хоста 
        # -----------------------------------------------

        if(!function_exists('curl_init')){
            $this->cancel(0, 'Library CURL as absent. Creation of site map imposable' , $log_message = '', true);
        }

        if (!$this->userId) $this->parent_inputData(); # parent::inputData

        if (!$this->checkParsingTable()){
            $this->cancel(0, 'You have problem with database table parsing_data', $log_message = '', true);
        }
       
        set_time_limit(0); # (0) снимает ограничения времени выполнения скрипта 

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

        foreach ($table_rows as $name => $item) {
            $table_rows[$name] = '';
        }

        $this->model->edit('parsing_data', [    #- обнуления строк БД 
            'fields' => $table_rows
        ]);
        

        $this->parsing(SITE_URL);
        
        if($this->all_links){
            foreach($this->all_links as $key => $link){
                if(!$this->filter($link) 
                    || in_array($link, $this->bad_links)) unset($this->all_links[$key]); 
            }
        }

        $this->createSitemap();

        if($redirect){
            !$_SESSION['res']['answer'] && $_SESSION['res']['answer'] = '<div class="gn-item gn-before gn-success">
                                                                            <span><i class="gn-icon gn-success-color icon-checkmark-circle"></i></span>
                                                                            <span class="gn-msg gn-success-color"><b>Well done! </b> Sitemap is crealed.</span> 
                                                                            <span class="gn-btn-close">
                                                                            <span class="gn-close gn-success-color-hover"><i class="gn-close-icon gn-success-color icon-cross"></i></span>
                                                                            </span>
                                                                        </div>';

            $redirect = PATH . Settings::get('routes')['admin']['alias'] . '/sitemap';
            $this->redirect(); 
 
        }else{
            $this->cancel('1', 'Site is created! ' . count($this->all_links) . ' links', '', true);
        }
        
    }

# -------------------- PARSING ---------------------------------------------------

    protected function parsing($urls)
    {    
        if(!$urls) return;

        $urls = (array)$urls;

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
            
        }while($status === CURLM_CALL_MULTI_PERFORM || $active); # $active > 3
        
        $result = [];
        
        foreach($urls as $i => $url){

           $result[$i] = curl_multi_getcontent($curl[$i]);
           curl_multi_remove_handle($curlMulty, $curl[$i]); # удаление дескрипторов(cURL_MULTI, cURL)
           curl_close($curl[$i]);

            # Регулярные выражения
            #   
            #  preg_match/выполняет проверку на соответствие регулярные  
            #  u/поиск и по многобаитовым кодировкам 
            #  i/регистры 
            #  s+/пробел 1 и более раз 
            #  \/экронирование
            #  d/спецсимволы, цифры 
            #  .?/точка, ? -может быть или нет 
             
           
            if (!preg_match('/Content-Type:\s+text\/html/ui', $result[$i])) {

                $this->bad_links[] = $url;
                
                $this->cancel(0, 'Incorrect content type ' . $url);

                continue;
            }

            #        ex. /HTTP/1.1 206    /HTTP/1.?1?__206
            if (!preg_match('/HTTP\/\d\.?\d?\s+20\d/ui', $result[$i])) {

                $this->bad_links[] = $url;

                $this->cancel(0, 'Incorrect server code ' . $url);

                continue;
            }

            $this->createLinks($result[$i]);
       }

       curl_multi_close($curlMulty);

    }

# -------------------- CREATE LINKS ----------------------------------------------

    protected function createLinks($content)
    {
        if($content){

            preg_match_all('/<a\s*?[^>]*?href\s*?=(["\'])(.+?)\1[^>]*?>/ui', $content, $links); 

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

                        $this->temp_links[] = $link;
                        $this->all_links[] = $link;
                    }
                }
            }  
        }
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

                            if(preg_match('/^[^\?]*' . $item . '/ui',  $link)){
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

            $this->writeLog($log_message, $file = 'parsing_log.txt');
        }

        if($exit){
            $alert['message'] = '<div class="' . $class . '">' . $alert['message'] . '</div>' ;
            

            exit(json_encode($alert));
        }
    }
    
# -------------------- CREATE SITE MAP -------------------------------------------

    protected function createSitemap()
    {
        $dom = new \DOMDocument('1.0', 'utf-8'); # представляет все содержимое HTML- или XML-документа

        $dom->formatOutput = true; # форматирует вывод, добавляя отступы и дополнительные пробелы

        $root = $dom->createElement('urlset'); # <urlset>элемент</urlset>

        $root->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $root->setAttribute('xmlns:xsi', 'http://w3.org/2001/XMLSchema-instance');
        $root->setAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');

        $dom->appendChild($root);

        $exs = simplexml_import_dom($dom); # экранирует - элемент
        
        if($this->all_links){

            $date = new \DateTime();
            $lastMod = $date->format('Y-m-d') . 'T' . $date->format('H:i:s+01:00');
                    # (array) 0: http://site.org/page/
            foreach($this->all_links as $item){
                           # page   <=   /page/  <=  http://site.org/page/
                $elem = trim(mb_substr($item, mb_strlen(SITE_URL)), '/');

                $elem = explode('/', $elem);

                $count = '0.' . (count($elem) - 1);

                $priority = 1 - (float)$count;

                if($priority == 1) $priority = '1.0';

                $urlMain = $exs->addChild('url');  # <url>элемент</url>

                $urlMain->addChild('loc', htmlspecialchars($item));  # <loc>http://site.org/page/</loc>

                $urlMain->addChild('lastmod', $lastMod);

                $urlMain->addChild('changefreq', 'weekly'); # вхождение раз в неделю | 'daily' - ежедневно

                $urlMain->addChild('priority', $priority);

            }
        }

        $dom->save($_SERVER['DOCUMENT_ROOT'] . PATH . 'sitemap.xml');

    }
}

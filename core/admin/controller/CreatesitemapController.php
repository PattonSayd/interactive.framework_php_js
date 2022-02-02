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
    protected $max_links = 200;

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
        'url' => ['en'],   //- исключаемые ссылки 
        'get' => []
    ];

# -------------------- INPUT DATA ------------------------------------------------ 

    public function inputData($links_counter = 1, $redirect = true)
    {   
        if(!function_exists('curl_init')){

            $this->writeLog('Oтсутствует библиотека CURL');

            $_SESSION['res']['answer'] = '<div class="alert alert-info alert-styled-left alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                <span class="font-weight-semibold">Warning! </span>Library CURL apsent. Creation of sitemap imposible.</span></div>'; 
            $this->redirect();
        }

        set_time_limit(0);

        if(file_exists($_SERVER['DOCUMENT_ROOT'] . PATH . 'log/' . $this->parsingLogFile))
            @unlink($_SERVER['DOCUMENT_ROOT'] . PATH . 'log/' . $this->parsingLogFile);

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
        if($urls === '/' || $urls === SITE_URL . '/') return; 

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $urls);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_TIMEOUT, 120);
        curl_setopt($curl, CURLOPT_RANGE, 0 - 4194304);

        $out = curl_exec($curl);

        curl_close($curl);

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

                        if (preg_match('/' . $ext . '(\s*?$|\?[^\/]*$)/ui', $link)) { // +++++++++++++++++++++++
                            continue 2;
                        }
                    }
                }

                if (strpos($link, '/') === 0) {
                    $link = SITE_URL . $link;
                }

                $site_url = mb_str_replace('.', '\.', mb_str_replace('/', '\/', SITE_URL));

                if (!in_array($link, $this->bad_links) && !preg_match('/^(' . $site_url . ')?\/?#[^\/]*?$/ui', $link) && strpos($link, SITE_URL) === 0 && !in_array($link, $this->all_links)) {

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
        
    }
    
# -------------------- CREATE SITE MAP -------------------------------------------

    protected function createSitemap()
    {
        

    }
}

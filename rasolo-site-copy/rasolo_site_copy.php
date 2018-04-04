<?php
/*
Plugin Name: RaSolo site copy
Plugin URI: http://ra-solo.ru
Description: Плагин копирует данные сайта и выкладывает ссылки пользователю для бекапирования
Version: 1.1
Author: Andrew Galagan
Author URI: http://galagan.ra-solo.ru
License: GPL2
*/

/*  Copyright YEAR  PLUGIN_AUTHOR_NAME  (email : eastern@ukr.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require_once(dirname(__FILE__) . '/srv_functions.php');
if(is_admin()){
	if(!is_action_exists('admin_notices','rasolo_display_admin_messages')){
			add_action('admin_notices', 'rasolo_display_admin_messages');
	};
};
	

// Copies
if(is_admin()){
    add_action('admin_menu','rasolositecopy_add_menu_3');
};
function rasolositecopy_add_menu_3()
       {
if(!current_user_can('edit_others_posts'))return;
add_options_page('The Ra-Solo options page',
                    'Ra-Solo: бекап',
                    'edit_pages',
                     __FILE__,
                    'rasolositecopy_options_copies_page');

       };   // The end of rasolositecopy_add_menu_3

function rasolositecopy_read_copies_dir()
       {

if(strpos($_SERVER['DOCUMENT_ROOT'],'httpdocs')!==false){
    if(!defined('HostingProvider'))define('HostingProvider','mchost');
    if(!defined('MysqlServer'))define('MysqlServer','a111834.mysql.mchost.ru');
} else {
    if(!defined('HostingProvider'))define('HostingProvider','citynet');
    if(!defined('MysqlServer'))define('MysqlServer','localhost');
};
if(!defined('data_file'))define('data_file',get_domain_core().'_files');
if(!defined('mysql_file'))define('mysql_file',get_domain_core().'_mysql');
$res_array=array(
                'copies_local_path'=>'/wp-content/uploads/user_copies',
                'copy_files_name'=>data_file.'.tar.gz',
                'is_copy_files'=>false,
                'copy_mysql_sql'=>mysql_file.'.sql',
                'copy_mysql_name'=>mysql_file.'.zip',
                'is_copy_mysql'=>false
                );
$res_array['copies_full_path']=$_SERVER['DOCUMENT_ROOT'].$res_array['copies_local_path'];
$res_array['mysql_full_path']=$_SERVER['DOCUMENT_ROOT'].$res_array['copies_local_path'];
$res_array['mysql_sql_full_path']=$res_array['mysql_full_path'].'/'.$res_array['copy_mysql_sql'];
$res_array['mysql_full_file']=$res_array['mysql_full_path'].'/'.$res_array['copy_mysql_name'];
$res_array['copies_full_file']=$res_array['copies_full_path'].'/'.$res_array['copy_files_name'];


if(!file_exists($res_array['copies_full_path'])){
    mkdir($res_array['copies_full_path']);
};

$local_time=current_time( 'timestamp' );
$time_diff=intval($local_time)-intval(time());

$rrr_counter=0;

if ($handle = opendir($res_array['copies_full_path'])) {

    while (false !== ($nth_file = readdir($handle))) {

        if(substr($nth_file,0,strlen(data_file))==data_file){

            $fl_size_b=intval(filesize($res_array['copies_full_path'].'/'.$nth_file));
            if($fl_size_b<9999){
                $fl_size=strval($fl_size_b).' б';
            } else if ($fl_size_b<1024*9999){
                $fl_size=strval(round($fl_size_b/1024,1)).' Кб';
            } else {
                $fl_size=strval(round($fl_size_b/1024/1024,1)).' Мб';
            };

            $res_array['copy_files_size']=$fl_size;
            $res_array['copy_files_time']=$time_diff+intval(filemtime($res_array['copies_full_path'].'/'.$nth_file));
            $res_array['copy_files_date']=date('Y-m-d H:i:s',$res_array['copy_files_time']);
//            $res_array['copy_files_name']=$nth_file;
            $res_array['is_copy_files']=true;
        };
        if(substr($nth_file,0,strlen(data_file))==mysql_file){

            $db_size_b=intval(filesize($res_array['copies_full_path'].'/'.$nth_file));
            if($db_size_b<9999){
                $db_size=strval($db_size_b).' б';
            } else if ($db_size_b<1024*9999){
                $db_size=strval(round($db_size_b/1024,1)).' Кб';
            } else {
                $db_size=strval(round($db_size_b/1024/1024,1)).' Мб';
            };


            $res_array['copy_mysql_size']=$db_size;
            $res_array['copy_mysql_time']=$time_diff+intval(filemtime($res_array['copies_full_path'].'/'.$nth_file));
            $res_array['copy_mysql_date']=date('Y-m-d H:i:s',$res_array['copy_mysql_time']);
//            $res_array['copy_mysql_name']=$nth_file;
            $res_array['is_copy_mysql']=true;
        };

    };

    closedir($handle);
};

return $res_array;
       };  // The end of rasolositecopy_read_copies_dir

function rasolositecopy_options_copies_page()
       {
if(!current_user_can('edit_others_posts'))return;
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
};

$files_data=rasolositecopy_read_copies_dir();

//myvar_dump($files_data,'$files_data');

if(!current_user_can('edit_others_posts'))return;
           ?><div class="wrap">
<h2 class="left_h1">Управление бекапом сайта</h2>
<h3 class="rs_right">&copy; &laquo;Ra-Solo&raquo;</h3>
<legend class="space_medium">Состояние файлов бекапа:</legend>


<fieldset class="options" id="rasolo_copy_state">
<table class="form-table" id="backup_copy_data">
    <tr valign="top">
        <th scope="row">
            <label>
 Наименование
            </label>
        </th>
        <td>
            Имя файла
        </td>
        <td>
            Размер
        </td>
        <td>
            Создан
        </td>
        <td>
            Операции
        </td>

    </tr>
    <tr valign="top">
        <th scope="row">
            <label>
 Архив файлов<br>сайта
            </label>
        </th>
        <td>
<?php
if($files_data['is_copy_files']){
    echo $files_data['copy_files_name'];
} else {
    echo 'Архив файлов отсутствует';
};
//$copy_files_date
?>
        </td>
        <td>
<?php
if($files_data['is_copy_files']){
    echo $files_data['copy_files_size'];
} else {
    echo 'Бекап-архив файлов сайта не был создан';
};
//
?>

        </td>
        <td>
<?php
if($files_data['is_copy_files']){
    echo $files_data['copy_files_date'];
} else {
    echo 'Бекап-архив файлов не был создан';
};
//
?>

        </td>
        <td>
            <?php
if($files_data['is_copy_files']){

    ?> <a href="<?php echo $files_data['copies_local_path'].'/'.$files_data['copy_files_name'];
            ?>">Скачать архив файлов сайта</a>
<?php

} else {
    echo 'Операции с архивом файлов сейчас не возможны';
};
//
?>
        </td>

    </tr>
    <tr valign="top">
        <th scope="row">
            <label>Архив базы<br>данных сайта
             </label>
        </th>
        <td>
<?php
if($files_data['is_copy_mysql']){
    echo $files_data['copy_mysql_name'];
} else {
    echo 'Бекап-архив БД отсутствует';
};
?>        </td>
        <td>
<?php
if($files_data['is_copy_mysql']){
    echo $files_data['copy_mysql_size'];
} else {
    echo 'Бекап-архив БД сайта не был создан';
};
//
?>
        </td>
        <td>
<?php
if($files_data['is_copy_mysql']){
    echo $files_data['copy_mysql_date'];
} else {
    echo 'Бекап-архив БД не создан';
};
//
?>
        </td>
        <td>
                        <?php
if($files_data['is_copy_mysql']){

    ?> <a href="<?php  echo $files_data['copies_local_path'].'/'.$files_data['copy_mysql_name'];
            ?>">Скачать архив БД сайта</a>
<?php

} else {
    echo 'Операции с архивом БД сейчас не возможны';
};
//
?>

        </td>


    </tr>
</table>
</fieldset>
<hr>
   <fieldset class="options" id="rasolo_make_copy">
<legend>Рекомендуется после создания свежих бекап-архивов сайта скачать оба файла на локальный диск вашего ПК.</legend>
<legend>
<?php
if($files_data['is_copy_files'] && $files_data['is_copy_mysql']){
  ?><h4>Внимание! После
 нажатия кнопки &laquo;Обновить архивы&raquo; старые файлы архивов
 будут удалены без процедуры подтверждения!</h4>
<?php
};

if($files_data['is_copy_files']
        || $files_data['is_copy_mysql']){
        ?><p>Если вы не скачивали текущий бекап, выполните следующие пункты:</p>
<ol>
<li>Скачайте бекап-архив файлов сайта (первая ссылка вверху страницы).</li>
<li>Скачайте бекап-архив базы данных сайта (вторая ссылка вверху страницы).</li>
<li>Сгенерируйте свежие бекап-архивы файлов и базы данных, для чего нажмите на кнопку
    &laquo;Обновить архивы&raquo;.</li>
<li>Скачайте свежий архив файлов сайта (первая обновленная ссылка вверху страницы).</li>
<li>Скачайте свежий архив базы данных сайта (вторая обновленная ссылка вверху страницы).</li>
</ol>
<?php
    };
    ?>

</legend>
<form method="post" class="space_medium">
<div class="rasolo_sitecopy_space">
<input class="button button-primary medium_left_margin rasolo_sitecopy_floatleft"
       name="create_site_copy" type="submit" value="<?php
if($files_data['is_copy_files'] && $files_data['is_copy_mysql']){
    echo 'Обновить архивы';
} else {
    echo 'Создать архивы';
};
?>" />
<?php
if($files_data['is_copy_files'] && $files_data['is_copy_mysql']){
?><input class="button button-primary medium_left_margin rasolo_sitecopy_floatright"
       name="delete_site_copy" type="submit" value="Удалить архивы" /></div>
<?php
};
    ?>

</form>
</fieldset>
</div>
<?php
       };   // The end of rasolositecopy_options_copies_page


add_action('after_setup_theme','rasolositecopy_process_post_data',13);
function rasolositecopy_process_post_data()
       {
if(!current_user_can('edit_others_posts'))return;
$copy_files_data=rasolositecopy_read_copies_dir();
//           myvar_dump($_POST,'$_POST');
if(isset($_POST['delete_site_copy'])){
    $files_full_name=$copy_files_data['copies_full_path'].'/'.$copy_files_data['copy_files_name'];
    $mysql_full_name=$copy_files_data['copies_full_path'].'/'.$copy_files_data['copy_mysql_name'];
    $was_existed=false;
    if($copy_files_data['is_copy_files']){
        $was_existed=true;
        unlink ($files_full_name);
    };
    if($copy_files_data['is_copy_mysql']){
        $was_existed=true;
        unlink ($mysql_full_name);
    };
    rasolo_set_admin_message('Архив'.
            ($was_existed?'ы успешно удалены':'ов не было'));
};

if(isset($_POST['create_site_copy'])){
    $files_full_name=$copy_files_data['copies_full_path'].'/'.$copy_files_data['copy_files_name'];
    $mysql_full_name=$copy_files_data['copies_full_path'].'/'.$copy_files_data['copy_mysql_name'];

    $was_existed=false;
    if($copy_files_data['is_copy_files']){
        $was_existed=true;
        unlink ($files_full_name);
    };
    if($copy_files_data['is_copy_mysql']){
        $was_existed=true;
        unlink ($mysql_full_name);
    };

    $db_password=preg_replace('/\$(.+)/', '\\\$$1', DB_PASSWORD);

    $cmd_arr=array(

        'mysql_create'=>'mysqldump --opt -u'.DB_USER.' -p'.$db_password.' -h'.
                (defined('DB_HOST')?DB_HOST:MysqlServer).
                ' '.DB_NAME.' > '.
                $copy_files_data['mysql_sql_full_path'],

        'files_copy'=>'tar -cf '.$copy_files_data['copies_full_file'].' '.
        $_SERVER['DOCUMENT_ROOT'].' --exclude='.$copy_files_data['copies_full_path']
        .'/*.*',

        'mysql_zip'=>'zip -r '.$copy_files_data['mysql_full_file'].' '.$copy_files_data['mysql_sql_full_path']



            );

//    unset($cmd_arr['files_copy']);
//    myvar_dump($cmd_arr,'$cmd_arr');
//    myvar_dump($copy_files_data,'$copy_files_data');
//    die();
//
    foreach ($cmd_arr as $cmd){
        @ob_start();
        system($cmd);
        $cmd_result = @ob_get_contents();
        @ob_end_clean();
//        myvar_dump($cmd_result);
    };

    if( file_exists($copy_files_data['mysql_sql_full_path'])){
        unlink($copy_files_data['mysql_sql_full_path']);
    };

    rasolo_set_admin_message('Бекап-файлы сайта и базы '.
            'данных успешно '.($was_existed?'обновлены':'созданы'));

//            die(' after ob_end_clean');
};

       };  // The end of  rasolositecopy_process_post_data

if(is_admin()){
    add_action( 'admin_enqueue_scripts', 'add_rasolositecopy_admin_style' );
};
function add_rasolositecopy_admin_style() {
    wp_enqueue_style( 'rasolo-site-copy-admin-style', this_plugin_url().'admin_style.css', false );
};

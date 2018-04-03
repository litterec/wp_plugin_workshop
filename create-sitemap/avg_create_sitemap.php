<?php
/*
Plugin Name: AVG sitemap
Plugin URI: http://ra-solo.ru
Description: Плагин генерирует карту сайта Wordpress
Version: 1.0
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

function get_taxonomy_url_list($inp_arr=array())
        {
//if($kind_of_taxonomy===false) return calc_query_numpag($type_of_the_post,$how_many_pst_on_page);
global $domain,$avg_onpage_output,$links_counter,$low_lnk_margin,$high_lnk_margin,$en;

if(!$avg_onpage_output && !current_user_can('manage_options'))return;

$kind_of_taxonomy=$inp_arr['extraloop'];
$type_of_the_post=$inp_arr['type'];
$how_many_pst_on_page=$inp_arr['postonpage'];

if($avg_onpage_output){
    $link_tmpl='<a href="@@@href@@@">@@@anch@@@</a>'.chr(10);
};
$out_str='';
$firsttime_output_trigger=true;

if($kind_of_taxonomy===false){

    $url_kernel='';
//    myvar_dump($this_numb_of_page,'$this_numb_of_page * * * ');

    $the_end_page_num=calc_query_numpag($type_of_the_post,$how_many_pst_on_page);
    if(!is_numeric($the_end_page_num))return false;
    if($the_end_page_num<1)return false;

    $link_tmpl_local=str_replace('<a href','<a title="'.$inp_arr['dscr'].
                '@@pg_num@@" href',$link_tmpl);

    for($ipage=1;$ipage<=$the_end_page_num;$ipage++){

        $links_counter++;

        if($avg_onpage_output
            && $firsttime_output_trigger
            && $links_counter>=$low_lnk_margin
            && $links_counter<=$high_lnk_margin){
            $firsttime_output_trigger=false;
            ?><h4><?php echo $inp_arr['subtitle']; ?></h4>
<?php
    };

        $link_href=$domain.$url_kernel.($ipage==1?'':'/page/'.$ipage.'/');
        if(!$avg_onpage_output){
            avg_put_line($link_href);
        } else {
            $link_tmpl_inside=str_replace('@@pg_num@@',', page '.$ipage,$link_tmpl_local);
            $this_link=str_replace('@@@href@@@',$link_href,$link_tmpl_inside);
//            $this_link=str_replace('@@@anch@@@',$anchor_txt.', p. '.$ipage,$this_link);  // страница
            $this_link=str_replace('@@@anch@@@',$links_counter,$this_link);  // страница
            if($links_counter>=$low_lnk_margin && $links_counter<=$high_lnk_margin){
                $out_str.=$this_link;
            };
        };

    };

    return $out_str;
} else if($kind_of_taxonomy=='category') {

//    $cat->term_id
    $args = array(
        'type'         => 'post',
        'child_of'     => 0,
        'parent'       => '',
        'orderby'      => 'name',
        'order'        => 'ASC',
        'hide_empty'   => 1,
        'hierarchical' => 1,
        'exclude'      => '',
        'include'      => '',
        'number'       => 0,
        'taxonomy'     => 'category',
        'pad_counts'   => false,
        // полный список параметров смотрите в описании функции http://wp-kama.ru/function/get_terms
    );
    $categories = get_categories( $args );
//    $categories = array_slice(get_categories( $args ),3,12);
//    myvar_dump($categories);
    foreach($categories as $nth_cat){
//        myvar_dump($nth_cat,'$nth_cat___$nth_cat____');
        $the_end_page_num=calc_query_numpag($type_of_the_post,
                    $how_many_pst_on_page,
                    $nth_cat->term_id);
//        myvar_dump($the_end_page_num,'$the_end_page_num___$the_end_page_num___');
        if(!is_numeric($the_end_page_num))continue;
        if($the_end_page_num<1)continue;
        if($avg_onpage_output){
            if($en && !is_numeric(substr($nth_cat->slug,0,1))){
                $this_cat_name=get_ml_cat_name($nth_cat->term_id);
                if(empty($this_cat_name))$this_cat_name='The secret translation for category: '.
                            $nth_cat->name;
            } else {
                $this_cat_name=$nth_cat->name;
            };
            $cat_page_title=str_replace('@@@taxonomy@@@',$this_cat_name,$inp_arr['dscr']);
        };

        $url_kernel='/'.$nth_cat->slug;
//        $anchor_txt='Кат. '.$nth_cat->name;  // Картинки по категории

        $out_str.='';
        for($ipage=1;$ipage<=$the_end_page_num;$ipage++){

            $links_counter++;

            if($avg_onpage_output
                && $firsttime_output_trigger
                && $links_counter>=$low_lnk_margin
                && $links_counter<=$high_lnk_margin){
                $firsttime_output_trigger=false;
                ?><h4><?php echo $inp_arr['subtitle']; ?></h4>
            <?php
            };

            $link_href=$domain.$url_kernel.($ipage==1?'':'/page/'.$ipage.'/');
            if(!$avg_onpage_output){
                avg_put_line($link_href);
            } else {

                $this_link=str_replace('@@@href@@@',$link_href,$link_tmpl);

                $this_link=str_replace('<a href=','<a title="'.$cat_page_title.
                    ', p. '.$ipage.'" href=',$this_link);

//                $this_link=str_replace('@@@anch@@@',$anchor_txt.', p. '.$ipage,$this_link);  // страница
                $this_link=str_replace('@@@anch@@@',$links_counter,$this_link);  // страница
                if($links_counter>=$low_lnk_margin && $links_counter<=$high_lnk_margin){
                    $out_str.=$this_link;
                };
            };
        };
    };
    return $out_str;
} else if($kind_of_taxonomy=='tags') {

    $tags = get_tags(array(
        'hide_empty' => false
    ));

//    $tags = array_slice($tags,3,12);   // ***************!!!!!!!!!!!!!!!!!!!!!!!!!!!!11
//    $double_debug_girls=array();
//    foreach($tags as $nth_tag){
//        if($nth_tag->term_id==129 || $nth_tag->term_id==400){
//            $double_debug_girls[]=$nth_tag;
//        };
//    };
//    129
//    myvar_dump($tags,'$tags_____JJJJJJJ__');
//    foreach($double_debug_girls as $nth_tag){
    foreach($tags as $nth_tag){
//    foreach($tags as $nth_tag){
//        myvar_dump($nth_tag,'$nth_cat___$nth_tag____');
        $the_end_page_num=calc_query_numpag($type_of_the_post,
                                    $how_many_pst_on_page,
                                    $nth_tag->term_id);

//        myvar_dump($nth_tag,'$nth_tag__mmmm___');
//        myvar_dump($the_end_page_num,'$the_end_page_num');
//        myvar_dump($the_end_page_num,'$the_end_page_num___$the_end_page_num___');
        if(!is_numeric($the_end_page_num))continue;
        if($the_end_page_num<1)continue;

        if($avg_onpage_output){

            if($en){
                $tag_ttl=get_term_meta($nth_tag->term_id,'eng_tag_name',1);
                if(empty($tag_ttl)){
                    if(preg_match('/[a-z0-9 -_]/i', $nth_tag->name)){
                        $tag_ttl=$nth_tag->name;
                    } else {
                        $tag_ttl='Unknown tag translation for '.$nth_tag->name;
                    };
                };
                $tag_page_title=str_replace('@@@taxonomy@@@',$tag_ttl,$inp_arr['dscr']);
            } else {
                $tag_page_title=str_replace('@@@taxonomy@@@',$nth_tag->name,$inp_arr['dscr']);
            };

        };

        $url_kernel='/tag/'.$nth_tag->slug;
//        $anchor_txt='Тег. '.$nth_tag->name;  // Картинки по категории

        $out_str.='';
        for($ipage=1;$ipage<=$the_end_page_num;$ipage++){


            $links_counter++;

            if($avg_onpage_output
                && $firsttime_output_trigger
                && $links_counter>=$low_lnk_margin
                && $links_counter<=$high_lnk_margin){
                $firsttime_output_trigger=false;
                ?><h4><?php echo $inp_arr['subtitle']; ?></h4>
            <?php
            };


            $link_href=$domain.$url_kernel.($ipage==1?'':'/page/'.$ipage.'/');
            if(!$avg_onpage_output){
                avg_put_line($link_href);
            } else {

                $this_link=str_replace('@@@href@@@',$link_href,$link_tmpl);

                $this_link=str_replace('<a href=','<a title="'.$tag_page_title.
                    ', p. '.$ipage.'" href=',$this_link);

    //            $this_link=str_replace('@@@anch@@@',$anchor_txt.', p. '.$ipage,$this_link);  // страница
                $this_link=str_replace('@@@anch@@@',$links_counter,$this_link);  // страница
                if($links_counter>=$low_lnk_margin && $links_counter<=$high_lnk_margin){
                    $out_str.=$this_link;
                };
            };
        };

    };
    unset($tags);
    return $out_str;

};

return false;
        };  // The end of get_taxonomy_url_list


function calc_query_numpag($posttype,$posts_per_page,$tax_id=false)
       {
// Determines the number of pages in particular request
if(!current_user_can('manage_options'))return;

if($posttype=='tag'){

   $args = array(
        'post_type' => 'post',
        'post_status' => 'publish',
        'tag_id' => $tax_id,
        'orderby' => 'id',
        'order' => 'ASC',
        'posts_per_page' => $posts_per_page
    );

    $related_posts = new WP_Query( $args );

    $max_num_pages = intval($related_posts->max_num_pages);
//    myvar_dump($posts_per_page,'$posts_per_page__vvv_');
//    myvar_dump($related_posts ,'$related_posts_numpag');

    unset($related_posts);
    return $max_num_pages;
};

$args = array(
    'post_type' => $posttype,
    'post_status' => 'publish',
    'paged' => 1,
    'posts_per_page' => $posts_per_page
);

//myvar_dump($tax_id,'$tax_id');

if($tax_id){
    $args['cat']=$tax_id;
};


//myvar_dump($args,'$args');

//    $args='post_type=any&posts_per_page=-1&post_status=publish';

$posts = new WP_Query($args);

$max_num_pages = intval($posts->max_num_pages);
//$how_many_posts=count($posts->posts);
//myvar_dump($how_many_posts,'$how_many_posts' );
//myvar_dump($posts_per_page,'$posts_per_page___444' );

//    $posts=array_slice($posts->posts,0,3);
//$cat_qv=$posts->query_vars;
//$cat_id=$cat_qv['cat'];
//myvar_dump($cat_id,'$cat_nm');
//if($cat_id==62){
//    myvar_dump($posts,'$posts' );
//};
//myvar_dump($max_num_pages,'$max_num_pages outside');
unset($posts);
return $max_num_pages;
       }; // the end of calc_query_numpag

function avg_put_line($line_url,$the_time=false)
       {
global $domain;
if(!current_user_can('manage_options'))return;

if(substr($line_url,0,4)<>'http'){
    $domain_wedge=$domain.'/';
} else {
    $domain_wedge='';
};
$lnk_tmpl='<url><loc>'.$domain_wedge.
    '@@@url@@@</loc><lastmod>@@@mod_time@@@</lastmod><changefreq>daily</changefreq>'.
    '<priority>1.00</priority></url>'.chr(10);

$page_outp=str_replace('@@@url@@@',$line_url,$lnk_tmpl);
$page_outp=str_replace('@@@mod_time@@@',avg_prepare_time($the_time),$page_outp);
avg_output($page_outp,true);
       };  // The end of avg_put_line

function avg_prepare_time($the_time=false)
       {
if($the_time===false){
   $time_lc=time();
} else {
   $time_lc=$the_time;
}
if(!is_numeric($time_lc))return 'Time format error';
return date('Y-m-d\TH:i:s',$time_lc).'+00:00';
       };  // The end of avg_prepare_time

function avg_generate_sitemap()
        {
global $avg_out_onscreen,$avg_gsm_header,$domain,$avg_onpage_output,
       $links_number,$pages_desired,$links_counter,$sitemap_pagenum,
       $low_lnk_margin,$high_lnk_margin,$en;
if(!$avg_onpage_output && !current_user_can('manage_options'))return;

$sitemap_pagenum = filter_input(INPUT_GET, 'smp', FILTER_SANITIZE_STRING);  //Sitemap page number
if(!is_numeric($sitemap_pagenum) || $sitemap_pagenum<1 )$sitemap_pagenum=1;
$low_lnk_margin=($sitemap_pagenum-1)*$links_number+1;
$high_lnk_margin=$sitemap_pagenum*$links_number;

require_once( get_template_directory() . '/srv_functions.php');
require_once( get_template_directory() . '/modules/simple_html_dom.php');

//$domain='http://beautypic.ru';
$domain='http://litterec.mcdir.ru';
// <url><loc>http://beautypic.ru/</loc><lastmod>2015-09-16T06:17:46+00:00</lastmod><changefreq>daily</changefreq><priority>1.00</priority></url>
//if(is_resource($avg_file_rsr))die('avg_generate_sitemap entrance: resource');
//else die('avg_generate_sitemap entrance: is not resource!');
if($avg_onpage_output){
  ?>
    <div id="onpage_smap">
    <h2><?php ere('Total list of this site pages','Общий перечень страниц сайта'); ?></h2>
<?
} else {
    avg_output($avg_gsm_header.chr(10),true);
};

$links_counter=0;

//die('avg_generate_sitemap middle');
//avg_put_line('');

$proj_list=array(
    'homepage'=>array(
            'type'=>'post',
            'subtitle'=>era('Main wallpaper list','Основной перечень обоев'),
            'extraloop'=>false,
            'postonpage'=>21,
            'dscr'=>era('The homepage pagination',
                       'Разбиение по страницам основного списка обоев на главной странице'),
            'anchor'=>era('The main page of the beautiful pictures site',
                        'Красивые обои на рабочий стол, главная страница')
                    ),
    'cats'=>array(
            'type'=>'post',
            'subtitle'=>era('The category pages','Страницы категорий'),
            'extraloop'=>'category',
            'postonpage'=>21,
            'dscr'=>'The category @@@taxonomy@@@ pagination',
            'anchor'=>era('The wallpapers by the category @@@taxonomy@@@',
                        'Обои по категории @@@taxonomy@@@')
                    ),
    'tags'=>array(
            'type'=>'tag',
            'subtitle'=>era('The tag pages','Страницы тегов'),
            'extraloop'=>'tags',
            'postonpage'=>21,
            'dscr'=>'The tags @@@taxonomy@@@ pagination',
            'anchor'=>era('The wallpapers by the tag @@@taxonomy@@@',
                          'Обои с тегом @@@taxonomy@@@')
                    ),
               );


/*
    'pages'=>array(
            'type'=>'page',
            'extraloop'=>false,
            'postonpage'=>21,
            'dscr'=>'The site pages',
            'anchor'=>'Страница сайта красивые обои '
                    ),
*/
if(!$avg_onpage_output && $avg_out_onscreen){
    ?><h3><?php ere('The site pages list','Перечень страниц сайта'); ?></h3><?php
};

//$rules = get_option( 'rewrite_rules' );
//myvar_dump($rules,'$rules');
//myvar_dump($wp_rewrite,'$wp_rewrite');
//die('$rules_____');

$args = array(
    'post_type' => 'page',
    'post_status' => 'publish'
);
$pages = new WP_Query($args);
$pages=$pages->posts;
//myvar_dump($links_counter,'$links_counter----666---');
//myvar_dump($low_lnk_margin,'$low_lnk_margin----666---');
//myvar_dump($high_lnk_margin,'$high_lnk_margin----666---');
$pages_url_list='';

$firsttime_output_trigger=true;
foreach ($pages as $nth_pg){
    $links_counter++;

    if($avg_onpage_output
        && $firsttime_output_trigger
        && $links_counter>=$low_lnk_margin
        && $links_counter<=$high_lnk_margin){
        $firsttime_output_trigger=false;
        ?><h4><?php ere('Static pages','Статические страницы'); ?></h4>
    <?php
    };

    $pst_id=$nth_pg->ID;
//    myvar_dump($nth_pg,'$nth_pg__kkkk___');
    $edited_time=strtotime($nth_pg->post_modified);
    if(!is_numeric($edited_time) || $edited_time<1201965663)$edited_time=time();
//    myvar_dump($edited_time,'$edited_time__kkkk___');
//    $lnk = get_post_meta( $nth_pg->ID, 'custom_permalink', true );
    $pt_mta = get_post_meta( $pst_id );
    if(empty($pt_mta['entitle'][0])){
        $entitle='An unknown translation for '.$nth_pg->post_title;
    } else {
        $entitle=$pt_mta['entitle'][0];
    };
//    myvar_dump($entitle,'$entitle__kkkk___');

    if( !is_array($pt_mta) || empty($pt_mta['custom_permalink'][0])){
       $lnk=$nth_pg->post_name;
    } else {
       $lnk=$pt_mta['custom_permalink'][0];
    };
    if($avg_onpage_output){
        if($links_counter>=$low_lnk_margin && $links_counter<=$high_lnk_margin){
            $pages_url_list.='<a title="'.era($entitle,$nth_pg->post_title).
                '" href="/'.$lnk.'">'.
                $links_counter.'</a>'.chr(10);
        };
    } else {
        avg_put_line($lnk,$edited_time);
    };
};
if($avg_onpage_output){
    if($pages_url_list)echo '<p>'.$pages_url_list.'</p>'.chr(10);
};
unset($pages);

// taxonomies output
foreach ($proj_list as $proj_key=>$nth_proj) {

    if($avg_out_onscreen){
        ?><h3>The data type <?php echo $nth_proj['type']; ?></h3><?php
    };

    if($avg_onpage_output){
        $this_taxonomy_url_list=get_taxonomy_url_list($nth_proj);
    } else {
        get_taxonomy_url_list($nth_proj);
    };

    if($avg_onpage_output){
//    $this_taxonomy_spec_char=htmlspecialchars($this_taxonomy_url_list);
//    myvar_dump($this_taxonomy_spec_char,'$this_taxonomy_spec_char');
        if($this_taxonomy_url_list){
//            echo '<p>$this_taxonomy_url_list----------------</p>';
    //    myvar_dump($nth_proj);
            echo $this_taxonomy_url_list;
//            echo '<p>The end of $this_taxonomy_url_list</p>';
        };
    };
};
// Single post output

if($avg_out_onscreen){
    ?><h3>The total post list</h3><?php
};
$args = array(
    'post_type' => 'post',
    'numberposts' => -1,
    'posts_per_page'   => 21,
    'paged'=>1,
    'post_status' => 'publish'
);
$posts = new WP_Query($args);
$max_pages=intval($posts->max_num_pages);
//myvar_dump($max_pages,'$max_pages for single');
$posts=$posts->posts;
$posts_count=count($posts);
//myvar_dump($posts_count,'$posts for single');
$posts_url_list='';

unset($posts);
$firsttime_output_trigger=true;

$iposts=0;
for($iseg=1;$iseg<=$max_pages;$iseg++){
    $args['paged']=$iseg;
    $posts = new WP_Query($args);
    $posts=$posts->posts;

    foreach ($posts as $nth_pst){
        $iposts++;
        $links_counter++;

        if($avg_onpage_output
            && $firsttime_output_trigger
            && $links_counter>=$low_lnk_margin
            && $links_counter<=$high_lnk_margin){
            $firsttime_output_trigger=false;
            ?><h4><?php ere('The post pages','Страницы публикаций'); ?></h4>
<?php
        };

//        myvar_dump($nth_pst,'__1111_____$nth_pst');
        $record_dom=str_get_html($nth_pst->post_content);
        if(isset($record_dom->find('a', 0)->href)){
            $main_img_url=$record_dom->find('a', 0)->href;
            if(!$avg_onpage_output){
                avg_put_line($main_img_url);
            };
//            myvar_dump($main_img_url,'$main_img_url');
        };
//        $ccc=htmlspecialchars($nth_pst->post_content);
//        myvar_dump($ccc,'$nth_pst->post_content');
//        if($iposts>20)break;
//        $pst_id=$nth_pst->ID;
//         $mta = get_post_meta( $nth_pst->ID, 'custom_permalink', true );
//        $mta = get_post_meta( $nth_pst->ID);
//        $mta=get_post_permalink($nth_pst->ID);
        $cats=get_the_category($nth_pst->ID);
        $cat_slug=false;
        foreach($cats as $cat){
            if(is_numeric(substr($cat->slug,0,1)))continue;
            $cat_slug=$cat->slug;
        };
        if(empty($cat_slug))$cat_slug='unknown_cat';

        $mta=$domain.'/'.$cat_slug.'/'.$nth_pst->ID.'-'.$nth_pst->post_name.'.html';
//        myvar_dump($nth_pst,'$nth_pst __zzzzz___');

        if($en){
            $pst_title=get_post_meta($nth_pst->ID, 'entitle', 1);
            if(empty($pst_title))$pst_title='The translation missed for: '.$nth_pst->post_title;
        } else {
            $pst_title=$nth_pst->post_title;
        }

        $edited_time=time();
        if(!$avg_onpage_output){
            avg_put_line($mta,$edited_time);
        } else {
            if($links_counter>=$low_lnk_margin && $links_counter<=$high_lnk_margin){
//                $posts_url_list.='<a title="Запись '.$nth_pst->post_title.'" href="'.$mta.'">'.$nth_pst->post_title.'</a>'.chr(10);
                $posts_url_list.='<a title="'.era('The post ','Запись ').$pst_title.
                    '" href="'.$mta.'">'.$links_counter.'</a>'.chr(10);
            };
        };

//        myvar_dump($mta ,'$mta __zzzzz___');
    //    $pt_mta = get_post_meta( $pst_id );
    //    myvar_dump($pt_mta,'$lnk__kkkk___');


    };  // The end of foreach by segment members

};  // The end of for by posts segments

if(!$avg_onpage_output){
    avg_output('</urlset>',true);
} else {
    if($posts_url_list)echo '<p>'.$posts_url_list.'</p>';
//    $links_number,$links_counter,$sitemap_pagenum;
//    $div_remainder=intval($links_counter%$links_number);
//    $this_page=1+intval(($links_counter-$div_remainder)/$links_number);
//    echo '<p>$links_counter='.$links_counter.'</p>';
//    echo '<p>$links_number='.$links_number.'</p>';
//    echo '<p>$this_page='.$this_page.'</p>';
//    echo '<p>$pages_desired='.$pages_desired.'</p>';
    ?>
</div><!-- EOD #onpage_smap -->
<?php


    ?><h4><?php ere('Sitemap page navigation','Навигация по страницам карты сайта'); ?>:</h4>
    <?php

    $_request_parameters = filter_input_array(
        INPUT_GET,
        array(
            'p' => FILTER_SANITIZE_URL,
            'mywnum' => FILTER_SANITIZE_URL,
            's' => FILTER_SANITIZE_URL,
            'smp' => FILTER_SANITIZE_URL,
            'lang' => FILTER_SANITIZE_URL,
            )
    );

?><div class="navigation"><?php
    for($ilnk=1;$ilnk<=$pages_desired;$ilnk++){
        $_request_parameters['smp']=$ilnk;
        $_request_arr=array();
        foreach($_request_parameters as $par_key=>$nth_param){
            if(!$nth_param)continue;
            $_request_arr[$par_key]=$par_key.'='.$nth_param;
        };
        if(count($_request_arr)){
            $_request_str='?'.implode('&',$_request_arr);
        } else {
            $_request_str='';
        };
        if($ilnk==$sitemap_pagenum){
            ?> <span class="page-numbers current"
 title="Текущая cтраница карты сайта."><?php echo $ilnk;
            ?></span>
        <?php
        } else {
           ?> <a class="page-numbers" title="Страница карты сайта номер <?php echo $ilnk;
           ?>."
 href="/sitemap<?php echo $_request_str; ?>"><?php echo $ilnk;
           ?></a>
        <?php

        };
    };

    ?></div><!-- EOD .navigation -->
<?php

};
       };  // the end of avg_generate_sitemap

function avg_output($output,$spec_char=false)
       {
global $avg_out_onscreen,$avg_file_rsr;
if(!current_user_can('manage_options'))return;

if($avg_out_onscreen){
    if($spec_char){
        echo htmlspecialchars($output);
    } else {
        echo $output;
    };
} else {
    if(!is_resource($avg_file_rsr))die('The $avg_file_rsr is not a proper file resource!');
    fwrite($avg_file_rsr,$output);
};
       };

function avg_options_page()
       {
//global $avg_file_rsr;
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
};
if(!current_user_can('manage_options'))return;
$this_options=avg_read_wp_options();

//myvar_dump($this_options,'$this_options_5555___',true);

?><div class="wrap">
<h2>Настройки AVG Site map</h2>
<form method="post">
<fieldset class="options" id="avg_cs_options">

<table class="form-table">
    <tr valign="top">
        <th scope="row">
            <label for="file_nm_root">
                Имя файла карты
            </label>
        </th>
        <td>
            <input value="<?php echo $this_options['file_nm_root'];
            ?>" type="text" name="file_nm_root" size="25" />
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">
            <label for="links_number">
                Количество ссылок на странице
            </label>
        </th>
        <td>
            <input value="<?php echo $this_options['links_number'];
            ?>" type="text" name="links_number" size="25" />
        </td>
    </tr>
    <tr valign="top">
        <th scope="row">
            <label for="pages_desired">
                Примерное количество страниц
            </label>
        </th>
        <td>
            <input value="<?php echo $this_options['pages_desired'];
            ?>" type="text" name="pages_desired" size="25" />
        </td>
    </tr>
</table>

    <legend>Параметры плагина AVG Sitemap:</legend>
    <input type="submit" value="Сохранить" />
</fieldset>
</form>

</div><!-- EOD wrap --><?php

       };  // The end of avg_options_page

// ex.after_setup_theme
add_action('after_setup_theme','avg_process_options',13);
function avg_process_options()
       {
global $avg_gsm_options,$avg_onpage_output;
$avg_onpage_output=false;
$avg_gsm_options=avg_read_wp_options();
if(!empty($_POST['file_nm_root'])){
    $avg_gsm_options['file_nm_root']=$_POST['file_nm_root'];
};
if(!empty($_POST['links_number'])){
    $avg_gsm_options['links_number']=intval($_POST['links_number']);
    if($avg_gsm_options['links_number']>2000)$avg_gsm_options['links_number']=2000;
    if($avg_gsm_options['links_number']<100)$avg_gsm_options['links_number']=100;
};
if(!empty($_POST['pages_desired'])){
    $avg_gsm_options['pages_desired']=intval($_POST['pages_desired']);
    if($avg_gsm_options['pages_desired']>100)$avg_gsm_options['pages_desired']=100;
    if($avg_gsm_options['pages_desired']<1)$avg_gsm_options['pages_desired']=1;
};
avg_write_wp_options($avg_gsm_options);
       };  // The end of avg_process_options

// ex.after_setup_theme
add_action('after_setup_theme','avg_sitemap_generator',20);
function avg_sitemap_generator()
       {
global $avg_msg_mode,$avg_msg_content,$file_name,$avg_file_rsr,
       $file_download_script,$zip_name;
if(!current_user_can('manage_options'))return;

if(!empty($_GET['avg_act'])){

    $style_sheet_cnt=<<<AVG_GSM_STYLESHEET
<?xml version="1.0" encoding="UTF-8"?>

<!-- Google Sitemaps Stylesheets (GSStylesheets)
     Project Home: http://sourceforge.net/projects/gstoolbox
     Copyright (c) 2005 Baccou Bonneville SARL (http://www.baccoubonneville.com)
     License http://www.gnu.org/copyleft/lesser.html GNU/LGPL

     Created by Serge Baccou
     1.0 / 20 Aug 2005

     Changes by Andrew Galagan ( http://eng.ra-solo.ru/ )
     1.1 / 20 Aug 2005 - sorting by clicking on column headers
                       - open urls in new window/tab
                       - some stylesheet/CSS cleanup

     Changes by Tobias Kluge ( http://enarion.net/ )
     1.2 / 22 Aug 2005 - moved sitemap file and sitemap index file into one file gss.xsl
	 1.5 / 27 Aug 2005 - added js and css into xslt stylesheet; only gss.xsl is needed now

     Changes by Serge Baccou
     1.3 / 23 Aug 2005 - some XSLT cleanup
     1.4 / 24 Aug 2005 - sourceForge and LGPL links and logos
                       - sorting is working for siteindex (see gss.js)


     Andrew Galagan ( http://eng.ra-solo.ru/ )
     1.5a/ 31 Aug 2005 - added version number in footer
                       - removed images (don't allow tracking on other servers)
     1.5b/ 05 Jul 2006 - removed (unnecessary) link to (missing) CSS file
                       - moved necessary items from Googles CSS file intern
                       - javascript code not compatible with Opera 9.0
     1.6/  19 Nov 2006 - Changed namespace to http://www.sitemaps.org/schemas/sitemap/0.9

-->

<xsl:stylesheet version="2.0"
                xmlns:html="http://www.w3.org/TR/REC-html40"
                xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <xsl:output method="html" version="1.0" encoding="iso-8859-1" indent="yes"/>

  <!-- Root template -->
  <xsl:template match="/">
    <html>
      <head>
        <title>Google Sitemap File for </title>
		<style type="text/css">
		  <![CDATA[
			<!--
			body {
				font-family: arial, sans-serif;
				font-size: 0.8em;
				height:100%;
			}
			body * {
				font-size: 100%;
			}
			h1 {
				font-weight:bold;
				font-size:1.5em;
				margin-bottom:0;
				margin-top:1px; }

			h2 {
				font-weight:bold;
				font-size:1.2em;
				margin-bottom:0;
				color:#707070;
				margin-top:1px; }

			h3 {
				font-weight:bold;
				font-size:1.2em;
				margin-bottom:0;
				color:#000;
				margin-top:1px; }

			td, th {
				font-family: arial, sans-serif;
				font-size: 0.9em;
			}

			.header {
				font-weight: bold;
				font-size: 1.1em;
			}

			p.sml {
				font-size:0.8em;
				margin-top:0;
			}

			.data {
				border-collapse:collapse;
				border:1px solid #b0b0b0;
				margin-top:3px;
				width:100%;
				padding:5em;
			}

			.data td {
				border-bottom:1px solid #b0b0b0;
				text-align:left;
				padding:3px;
			}

			.sortup {
				background-position: right center;
				background-image: url(http://www.google.com/webmasters/sitemaps/images/sortup.gif);
				background-repeat: no-repeat;
				font-style:italic;
				white-space:pre; }

			.sortdown {
				background-position: right center;
				background-image: url(http://www.google.com/webmasters/sitemaps/images/sortdown.gif);
				background-repeat: no-repeat;
				font-style:italic;
				white-space:pre; }

			table.copyright {
				width:100%;
				border-top:1px solid #ddad08;
				margin-top:1em;
				text-align:center;
				padding-top:1em;
				vertical-align:top; }

			.copyright {
				color: #6F6F6F;
				font-size: 0.8em;
			}
			-->
		  ]]>
		</style>
        <script language="JavaScript">
		  <![CDATA[
			var selectedColor = "blue";
			var defaultColor = "black";
			var hdrRows = 1;
			var numeric = '..';
			var desc = '..';
			var html = '..';
			var freq = '..';

			function initXsl(tabName,fileType) {
				hdrRows = 1;

			  if(fileType=="sitemap") {
			  	numeric = ".3.";
			  	desc = ".1.";
			  	html = ".0.";
			  	freq = ".2.";
			  	initTable(tabName);
				  setSort(tabName, 3, 1);
			  }
			  else {
			  	desc = ".1.";
			  	html = ".0.";
			  	initTable(tabName);
				  setSort(tabName, 1, 1);
			  }

				var theURL = document.getElementById("head1");
				theURL.innerHTML += ' ' + location;
				document.title += ': ' + location;
			}

			function initTable(tabName) {
			  var theTab = document.getElementById(tabName);
			  for(r=0;r<hdrRows;r++)
			   for(c=0;c<theTab.rows[r].cells.length;c++)
			     if((r+theTab.rows[r].cells[c].rowSpan)>hdrRows)
			       hdrRows=r+theTab.rows[r].cells[c].rowSpan;
			  for(r=0;r<hdrRows; r++){
			    colNum = 0;
			    for(c=0;c<theTab.rows[r].cells.length;c++, colNum++){
			      if(theTab.rows[r].cells[c].colSpan<2){
			        theCell = theTab.rows[r].cells[c];
			        rTitle = theCell.innerHTML.replace(/<[^>]+>|&nbsp;/g,'');
			        if(rTitle>""){
			          theCell.title = "Change sort order for " + rTitle;
			          theCell.onmouseover = function(){setCursor(this, "selected")};
			          theCell.onmouseout = function(){setCursor(this, "default")};
			          var sortParams = 15; // bitmapped: numeric|desc|html|freq
			          if(numeric.indexOf("."+colNum+".")>-1) sortParams -= 1;
			          if(desc.indexOf("."+colNum+".")>-1) sortParams -= 2;
			          if(html.indexOf("."+colNum+".")>-1) sortParams -= 4;
			          if(freq.indexOf("."+colNum+".")>-1) sortParams -= 8;
			          theCell.onclick = new Function("sortTable(this,"+(colNum+r)+","+hdrRows+","+sortParams+")");
			        }
			      } else {
			        colNum = colNum+theTab.rows[r].cells[c].colSpan-1;
			      }
			    }
			  }
			}

			function setSort(tabName, colNum, sortDir) {
				var theTab = document.getElementById(tabName);
				theTab.rows[0].sCol = colNum;
				theTab.rows[0].sDir = sortDir;
				if (sortDir)
					theTab.rows[0].cells[colNum].className='sortdown'
				else
					theTab.rows[0].cells[colNum].className='sortup';
			}

			function setCursor(theCell, mode){
			  rTitle = theCell.innerHTML.replace(/<[^>]+>|&nbsp;|\W/g,'');
			  if(mode=="selected"){
			    if(theCell.style.color!=selectedColor)
			      defaultColor = theCell.style.color;
			    theCell.style.color = selectedColor;
			    theCell.style.cursor = "hand";
			    window.status = "Click to sort by '"+rTitle+"'";
			  } else {
			    theCell.style.color = defaultColor;
			    theCell.style.cursor = "";
			    window.status = "";
			  }
			}

			function sortTable(theCell, colNum, hdrRows, sortParams){
			  var typnum = !(sortParams & 1);
			  sDir = !(sortParams & 2);
			  var typhtml = !(sortParams & 4);
			  var typfreq = !(sortParams & 8);
			  var tBody = theCell.parentNode;
			  while((tBody.nodeName!="TBODY") && (tBody.nodeName!="TABLE")) {
			    tBody = tBody.parentNode;
			  }
			  var tabOrd = new Array();
			  if(tBody.rows[0].sCol==colNum) sDir = !tBody.rows[0].sDir;
			  if (tBody.rows[0].sCol>=0)
			    tBody.rows[0].cells[tBody.rows[0].sCol].className='';
			  tBody.rows[0].sCol = colNum;
			  tBody.rows[0].sDir = sDir;
			  if (sDir)
			  	 tBody.rows[0].cells[colNum].className='sortdown'
			  else
			     tBody.rows[0].cells[colNum].className='sortup';
			  for(i=0,r=hdrRows;r<tBody.rows.length;i++,r++){
			    colCont = tBody.rows[r].cells[colNum].innerHTML;
			    if(typhtml) colCont = colCont.replace(/<[^>]+>/g,'');
			    if(typnum) {
			      colCont*=1;
			      if(isNaN(colCont)) colCont = 0;
			    }
			    if(typfreq) {
					switch(colCont.toLowerCase()) {
						case "always":  { colCont=0; break; }
						case "hourly":  { colCont=1; break; }
						case "daily":   { colCont=2; break; }
						case "weekly":  { colCont=3; break; }
						case "monthly": { colCont=4; break; }
						case "yearly":  { colCont=5; break; }
						case "never":   { colCont=6; break; }
					}
				}
			    tabOrd[i] = [r, tBody.rows[r], colCont];
			  }
			  tabOrd.sort(compRows);
			  for(i=0,r=hdrRows;r<tBody.rows.length;i++,r++){
			    tBody.insertBefore(tabOrd[i][1],tBody.rows[r]);
			  }
			  window.status = "";
			}

			function compRows(a, b){
			  if(sDir){
			    if(a[2]>b[2]) return -1;
			    if(a[2]<b[2]) return 1;
			  } else {
			    if(a[2]>b[2]) return 1;
			    if(a[2]<b[2]) return -1;
			  }
			  return 0;
			}

		  ]]>
		</script>

      </head>

      <!-- Store in @@@dollar@@@fileType if we are in a sitemap or in a siteindex -->
      <xsl:variable name="fileType">
        <xsl:choose>
		  <xsl:when test="//sitemap:url">sitemap</xsl:when>
		  <xsl:otherwise>siteindex</xsl:otherwise>
        </xsl:choose>
      </xsl:variable>

      <!-- Body -->
      <body onLoad="initXsl('table0','{@@@dollar@@@fileType}');">

        <!-- Text and table -->
        <h1 id="head1">Google Sitemap</h1>
        <xsl:choose>
	      <xsl:when test="@@@dollar@@@fileType='sitemap'"><xsl:call-template name="sitemapTable"/></xsl:when>
	      <xsl:otherwise><xsl:call-template name="siteindexTable"/></xsl:otherwise>
  		</xsl:choose>

        <!-- Copyright notice -->
        <br/>
        <table class="copyright" id="table_copyright">
          <tr>
            <td>
              <p>Google Sitemaps: (c) 2005-2007 <a href="http://www.google.com">Google</a> - <a href="https://www.google.com/webmasters/sitemaps/stats">My Sitemaps</a> - <a href="http://www.google.com/webmasters/sitemaps/docs/en/about.html">About</a> - <a href="http://www.google.com/webmasters/sitemaps/docs/en/faq.html">FAQ</a> - <a href="http://groups-beta.google.com/group/google-sitemaps">Discussion</a> - <a href="http://sitemaps.blogspot.com/">Blog</a></p>
              Google Sitemaps Stylesheets v1.6: (c) 2005-2007 <a href="http://www.baccoubonneville.com">Baccou Bonneville</a> - <a href="http://sourceforge.net/projects/gstoolbox">Project</a> - <a href="http://www.baccoubonneville.com/blogs/index.php/webdesign/2005/08/20/google-sitemaps-stylesheets">Blog</a><br/>
              Andrew Galagan, Ra-Solo <a href="http://eng.ra-solo.ru">Ra-Solo web studio</a> - Tobias Kluge, enarion.net <a href="http://enarion.net/google/phpsitemapng">phpSitemapNG</a>
            </td>
          </tr>
        </table>
      </body>
    </html>
  </xsl:template>

  <!-- siteindexTable template -->
  <xsl:template name="siteindexTable">
    <h3>This sitemap index file was created by <a href="http://eng.ra-solo.ru">Ra-Solo web studio</a>.</h3>
    <h2>Number of sitemaps in this Google sitemap index: <xsl:value-of select="count(sitemap:sitemapindex/sitemap:sitemap)"></xsl:value-of></h2>
    <p class="sml">Click on the table headers to change sorting.</p>
    <table border="1" width="100%" class="data" id="table1">
      <tr class="header">
        <td>Sitemap URL</td>
        <td>Last modification date</td>
      </tr>
      <xsl:apply-templates select="sitemap:sitemapindex/sitemap:sitemap">
        <xsl:sort select="sitemap:lastmod" order="descending"/>
      </xsl:apply-templates>
    </table>
  </xsl:template>

  <!-- sitemapTable template -->
  <xsl:template name="sitemapTable">
    <h3>This Google Sitemap file was created by <a href="http://eng.ra-solo.ru">Ra-Solo web studio AVG sitemap plugin</a>.</h3>
    <h2>Number of URLs in this Google Sitemap: <xsl:value-of select="count(sitemap:urlset/sitemap:url)"></xsl:value-of></h2>
    <p class="sml">Click on the table headers to change sorting.</p>
    <table border="1" width="100%" class="data" id="table0">
	  <tr class="header">
	    <td>Sitemap URL</td>
		<td>Last modification date</td>
		<td>Change freq.</td>
		<td>Priority</td>
	  </tr>
	  <xsl:apply-templates select="sitemap:urlset/sitemap:url">
	    <xsl:sort select="sitemap:priority" order="descending"/>
	  </xsl:apply-templates>
	</table>
  </xsl:template>

  <!-- sitemap:url template -->
  <xsl:template match="sitemap:url">
    <tr>
      <td>
        <xsl:variable name="sitemapURL"><xsl:value-of select="sitemap:loc"/></xsl:variable>
        <a href="{@@@dollar@@@sitemapURL}" target="_blank" ref="nofollow"><xsl:value-of select="@@@dollar@@@sitemapURL"></xsl:value-of></a>
      </td>
      <td><xsl:value-of select="sitemap:lastmod"/></td>
      <td><xsl:value-of select="sitemap:changefreq"/></td>
      <td><xsl:value-of select="sitemap:priority"/></td>
    </tr>
  </xsl:template>

  <!-- sitemap:sitemap template -->
  <xsl:template match="sitemap:sitemap">
    <tr>
      <td>
        <xsl:variable name="sitemapURL"><xsl:value-of select="sitemap:loc"/></xsl:variable>
        <a href="{@@@dollar@@@sitemapURL}"><xsl:value-of select="@@@dollar@@@sitemapURL"></xsl:value-of></a>
      </td>
      <td><xsl:value-of select="sitemap:lastmod"/></td>
    </tr>
  </xsl:template>

</xsl:stylesheet>
AVG_GSM_STYLESHEET;


    $upl_dir=wp_upload_dir();
    $upl_basedir=$upl_dir['basedir'];
    $file_dir_name=$upl_basedir.'/'.$file_name;
    $gss_xsl_name=$upl_basedir.'/gss.xsl';
    $upl_baseurl=$upl_dir['baseurl'];
    $file_url_name=$upl_baseurl.'/'.$file_name;
//    myvar_dump($file_dir_name);
//    myvar_dump($file_url_name);

    if( file_exists($gss_xsl_name)){
        unlink($gss_xsl_name);
    };
    $avg_css_rsr=fopen($gss_xsl_name,'w');
    fwrite($avg_css_rsr,str_replace('@@@dollar@@@','$',$style_sheet_cnt));
    fclose($avg_css_rsr);
    unset($avg_css_rsr);

    if( file_exists($file_dir_name)){
        unlink($file_dir_name);
    };
//    if( file_exists($file_dir_name.'.gz')){
//        unlink($file_dir_name.'.gz');
//    };
    $avg_file_rsr=fopen($file_dir_name,'w');
//    fwrite($avg_file_rsr,'test');
//    if(is_resource($avg_file_rsr))echo '<p>$avg_file_rsr - isssssssssss resource</p>';
//    else echo '<p>$avg_file_rsr - isntttttt resource</p>';
    avg_generate_sitemap();
    fclose($avg_file_rsr);

    $avg_read_file_cnt=file_get_contents($file_dir_name);
    $avg_gz_file_cnt=gzencode($avg_read_file_cnt);
    unset($avg_read_file_cnt);
    file_put_contents($file_dir_name.'.gz',$avg_gz_file_cnt);

    $avg_file_size=strval(filesize($file_dir_name));
    $avg_zip_file_size=strval(filesize($file_dir_name.'.gz'));

    $zip_dir_name=$upl_basedir.'/'.$zip_name;
    $zip_url_name=$upl_baseurl.'/'.$zip_name;

    if(file_exists($zip_dir_name)){
        unlink($zip_dir_name);
    };
    $avg_zip = new ZipArchive();

    if ($avg_zip->open($zip_dir_name, ZipArchive::CREATE)!==TRUE) {
           exit('<p>It\'s impossible to create '.$zip_name.'</p>');
    };

    if(file_exists($file_dir_name)){
        $avg_zip->addFile($file_dir_name,$file_name);
    };

    if(file_exists($file_dir_name.'.gz')){
        $avg_zip->addFile($file_dir_name.'.gz',$file_name.'.gz');
    };

    if(file_exists($upl_basedir.'/gss.xsl')){
        $avg_zip->addFile($upl_basedir.'/gss.xsl','gss.xsl');
    };

    unset($avg_zip);

//    myvar_dump($avg_file_size,'$avg_file_size');
    if($avg_file_size>0 && $avg_zip_file_size>0){
        $avg_kb_size=bcdiv($avg_file_size, '1024', 1);
        $avg_zip_kb_size=bcdiv($avg_zip_file_size, '1024', 1);
//        myvar_dump($avg_file_size,'$avg_file_size');
//        myvar_dump($avg_kb_size,'$avg_kb_size');
        $avg_msg_mode='info';
        $avg_msg_content='<h3>Google sitemap has been created successfully. '.
            'The file size is '.$avg_kb_size.' Kb ('.
            $avg_zip_kb_size.' in arch.)</h3>'.chr(10).
            '<p><a class="button-secondary" target="_blank" href="'.$file_url_name
            .'">Открыть AVG sitemap file</a></p>'.chr(10).
            $file_download_script.chr(10).
        '<div>'.chr(10).
        '<button type="submit" onclick="return send_avg_sitemap(\''.$zip_url_name.
                '\');">Скачать '.chr(10).
        'новый AVG sitemap file!</button>'.chr(10).'</div>';

    } else {
        $avg_msg_mode='error';
        $avg_msg_content='<h3>The error has been occured while '.
                'sitemap file creation...</h3>'.chr(10);

    };
    add_action('admin_notices', 'avg_display_info_notice');
};

       };  // The end of  avg_sitemap_generator

function avg_show_sitemap()
       {
global $avg_onpage_output;
$avg_onpage_output=true;
avg_generate_sitemap();
// Shows sitemap on the page while to be called from page tamplate
//list_hooks();
return;
       };  // The end of avg_show_sitemap


function avg_display_info_notice()
       {
global $avg_msg_mode,$avg_msg_content;
if(!current_user_can('manage_options'))return;
$msg_types=array(
    'info'=>'updated',
    'error'=>'error',
    'warning'=>'update-nag'
);
if(!in_array($avg_msg_mode,array_flip($msg_types))){
    $avg_msg_mode='info';
};
if(empty($avg_msg_content))$avg_msg_content='Unknown message content';

?>
<div class="<?php echo $msg_types[$avg_msg_mode]; ?> notice">
    <p><?php _e( $avg_msg_content, 'avg_create_sitemap' ); ?></p>
</div>
<?php
       } // the end of avg_display_info_notice


function avg_write_wp_options($opt_arr=array())
       {
global $my_options_name;
if(!is_array($opt_arr))return false;

$my_options_to_write=serialize($opt_arr);

//myvar_dump($opt_arr,'$opt_arr');
//myvar_dump($my_options_name,'$my_options_name');
//die('$my_options_name3233');

update_option( $my_options_name,$my_options_to_write);

//$tmp=get_option($my_options_name);
//$tmp1=unserialize($tmp);
//myvar_dump($opt_arr,'$avg_gsm1_options');
//myvar_dump($my_options_name,'$my_options_name');
//myvar_dump($tmp,'$tmp');
//myvar_dump($tmp1,'$tmp1');
//die('Dying $avg_gsm_options');

       };  // The end of avg_write_wp_options

function avg_read_wp_options()
       {
global $my_options_name,$avg_gsm_options;
$default_avg_options=array(
    'file_nm_root'=>'avg_ggl_sitemap',
    'links_number'=>100,
    'pages_desired'=>3
);

$avg_gsm_options_data=get_option($my_options_name);
$avg_gsm_options=@unserialize($avg_gsm_options_data);

$slight_difference=array_diff($avg_gsm_options,$default_avg_options);
if(count($slight_difference)){
    $avg_gsm_options=array_merge($avg_gsm_options,$slight_difference);
};

//myvar_dump($avg_gsm_options_data,'$avg_gsm_options_data ___777___');
//myvar_dump($avg_gsm_options,'$avg_gsm_options ___777___');
//myvar_dump($my_options_name,'$my_options_name ___777___');
//die('diying___777___');

if(!is_array($avg_gsm_options)){
    avg_write_wp_options($default_avg_options);
    $avg_gsm_options=$default_avg_options;
};
return $avg_gsm_options;
       }; // The end of avg_read_wp_options

// ex.after_setup_theme (activate_blog - too early)
add_action('after_setup_theme','avg_sitemap_init',12);
function avg_sitemap_init()
       {
global $file_name,$my_options_name,
       $file_download_script,$zip_name,$avg_gsm_header,$avg_out_onscreen,
       $default_avg_options,$links_number,$pages_desired;

$my_options_name='avg_gsm_options';

$avg_gsm_options=avg_read_wp_options();
if(!is_array($avg_gsm_options)){
    avg_write_wp_options($default_avg_options);
    $avg_gsm_options=$default_avg_options;
};
$file_nm_root=$avg_gsm_options['file_nm_root'];
$links_number=$avg_gsm_options['links_number'];
$pages_desired=$avg_gsm_options['pages_desired'];

$avg_out_onscreen=false;

//$file_nm_root='avg_ggl_sitemap';
$file_name=$file_nm_root.'.xml';
$zip_name=$file_nm_root.'.zip';

$avg_gsm_header=<<<AVG_GSM_HEADER
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="gss.xsl"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.google.com/schemas/sitemap/0.84 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
AVG_GSM_HEADER;

$file_download_script=<<<FILEDOWNLOAD
<script>
function send_avg_sitemap(link_href)
        {
var filename = link_href.replace(/^.*[\\\/]/, '');
var link = document.createElement('a');
link.download = filename;
link.href = link_href;
document.body.appendChild(link);
link.click();
document.body.removeChild(link);
return false;
        }
</script>
FILEDOWNLOAD;

       };   // The end of avg_sitemap_init

// ============================ The main part

global $avg_out_onscreen;

function gsm_add_menu() {

    add_menu_page( 'AVG create Google sitemap page',
               'Создать AVG-s-m',
               'moderate_comments',
    'create_avg_sm', 'avg_create_sitemap_admin_page',
            'dashicons-list-view' );


    add_options_page('The AVG Gsm options page',
                    'AVG Gsm options',
                    'edit_pages',
                     __FILE__,
                    'avg_options_page');
}
add_action('admin_menu', 'gsm_add_menu');



function avg_create_sitemap_admin_page()
       {
global $file_name,$file_download_script,$zip_name,$avg_gsm_options,$avg_onpage_output;
$avg_onpage_output=false;
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
};
if(!current_user_can('manage_options'))return;

?><div class="wrap">
<h2>Создание карты сайта Google site map</h2>

<div id="create_button_block">
   <a class="button-secondary" href="<?php
//   echo admin_url("/options-general.php?page=create-sitemap%2Favg_create_sitemap.php&avg_act=create_ggl_smp");
   echo admin_url("admin.php?page=create_avg_sm&avg_act=create_ggl_smp");
   ?>" title="Create Google Sitemap immediately">Create Google Sitemap</a>
</div>


           <?php

    $upl_dir=wp_upload_dir();
    $upl_basedir=$upl_dir['basedir'];
    $file_dir_name=$upl_basedir.'/'.$file_name;
    $zip_dir_name=$upl_basedir.'/'.$zip_name;
    $upl_baseurl=$upl_dir['baseurl'];
    $zip_url_name=$upl_baseurl.'/'.$zip_name;
    $file_url_name=$upl_baseurl.'/'.$file_name;

    if(empty($_GET['avg_act']) && file_exists($file_dir_name)){

//        myvar_dump($avg_gsm_options,'$avg_gsm_options');

        $fle_time=filemtime($file_dir_name);
        ?><div id="avg_file_exists">
        <h3>Файл карты сайта Google Sitemap «<?php echo $avg_gsm_options['file_nm_root'];
            ?>» уже существует!</h3>
<?php
        $avg_file_size=strval(filesize($file_dir_name));
//    myvar_dump($avg_file_size,'$avg_file_size');
        if($avg_file_size>0){
            $avg_kb_size=bcdiv($avg_file_size, '1024', 1);

            if(file_exists($zip_dir_name)){
                unlink($zip_dir_name);
            };
            $avg_zip = new ZipArchive();

            if ($avg_zip->open($zip_dir_name, ZipArchive::CREATE)!==TRUE) {
                   exit('<p>It\'s impossible to create '.$zip_name.'.</p>');
            };

            if(file_exists($file_dir_name)){
                $avg_zip->addFile($file_dir_name,$file_name);
            };

            if(file_exists($file_dir_name.'.gz')){
                $avg_zip->addFile($file_dir_name.'.gz',$file_name.'.gz');
            };

            if(file_exists($upl_basedir.'/gss.xsl')){
                $avg_zip->addFile($upl_basedir.'/gss.xsl','gss.xsl');
            };

            unset($avg_zip);

// (< ?php echo $avg_zip->numFiles; ? >)

        ?><h4>Размер файла: <?php echo $avg_kb_size;
                ?> кБ, дата создания: <?php echo date('d\/m\/Y',$fle_time); ?></h4>
<p><a class="button-secondary" target="_blank" href="<?php echo $file_url_name;
        ?>">Открыть AVG sitemap file</a>
</p>
<?php
            echo $file_download_script;
//            echo str_replace('@@@filename@@@',$file_url_name,$file_download_script);
            ?>
<div>
<button type="submit" onclick="return send_avg_sitemap('<?php
echo $zip_url_name;
//echo $file_url_name;
            ?>');">Скачать AVG sitemap file!</button>
</div>
<?php

        } else {

            ?><h3>К сожалению, этот файл пустой</h3><?php
        };

        ?>

</div><!-- EOD avg_file_exists -->
<?php
  };

?>
</div><!-- EOD wrap --><?php


       }; // The end of avg_create_sitemap_admin_page
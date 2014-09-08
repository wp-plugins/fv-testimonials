<?php

add_action('admin_init', 'fv_testimonials_init_conv');

function fv_testimonials_init_conv() {

    /* converting everything from previous version */
   add_action( 'wp_ajax_fv_testimonials_ajax_convert_content', 'fv_testimonials_ajax_convert_content' );  // this is for converting Categories, Testimonials and Images
   add_action( 'wp_ajax_fv_testimonials_ajax_convert_shortcodes_standard', 'fv_testimonials_ajax_convert_shortcodes_standard' );  // this will convert shortcodes in Posts, Pages and in Template files
   add_action( 'wp_ajax_fv_testimonials_ajax_convert_shortcodes_db', 'fv_testimonials_ajax_convert_shortcodes_db');
}

/* * ************************************************************************************************ */
/* * ************************************************************************************************ */
/* * ************************************************************************************************ */
/* * ************************************************************************************************ */
/* function fv_testimonials_ajax_convert_content(){
  // remove whatever was there
  delete_option('_fvt_converted_categories');
  delete_option('_fvt_converted_testimonials');
  delete_option('_fvt_order');

  $convertcategories = fv_testimonials_ajax_convert_cats();
  $converttestimonials = fv_testimonials_ajax_convert_testimonials();
  if ($convertcategories && $converttestimonials) echo "OK";
  die();
  }

  function fv_testimonials_ajax_convert_shortcodes_standard(){
  $posts_converted = fv_testimonials_ajax_convert_shortcodes_posts();
  $theme_converted = fv_testimonials_ajax_convert_shortcodes_theme();

  if ($posts_converted && $theme_converted) echo $posts_converted . "<br />". $theme_converted;
  die();
  } */

function fv_testimonials_ajax_convert_cats() {
    global $wpdb;

    $max_index = (int) $wpdb->get_var("SELECT max(`parent_right`) as `max` FROM `wp_fpt_category` WHERE 1 LIMIT 1");


    $sorted = sort_category2(1, $max_index, 0);

    return $sorted;
}

function sort_category2($left, $right, $parent) {
    global $wpdb;
    while ($left < $right) {

        $category = $wpdb->get_row("SELECT `id`,`parent_left`,`parent_right`,`name` FROM `wp_fpt_category` WHERE `parent_left`=" . $left . " LIMIT 1");

        $newparent = wp_insert_term($category->name, "testimonial_category", array('parent' => $parent));
        //
        if ($newparent->errors)
            return false;
        $aConvertedCats = get_option("_fvt_converted_categories");
        if (!$aConvertedCats)
            $aConvertedCats = array();
        $aConvertedCats[(int) $category->id] = (int) $newparent['term_id'];
        update_option("_fvt_converted_categories", $aConvertedCats);

        if ((int) $category->parent_left + 1 < (int) $category->parent_right)
            sort_category2((int) $category->parent_left + 1, (int) $category->parent_right, $newparent['term_id']);

        $left = (int) $category->parent_right + 1;
    }
    return true;
}

function fv_testimonials_ajax_convert_testimonials() {
    global $wpdb;

    $testimonials = $wpdb->get_results("SELECT * FROM `wp_fpt_testimonials` WHERE 1 ORDER BY `order` ASC");
    $categories = get_option("_fvt_converted_categories");
    $newOrder = array();

    foreach ($testimonials as $test) {

        $title = wp_strip_all_tags(preg_replace('/<br\s*\/?>/', ', ', $test->title)); //<?

        $post = array(
            'post_content' => $test->text,
            'post_excerpt' => $test->excerpt,
            'post_date' => $test->last_modified_date,
            'post_name' => $test->slug,
            'post_title' => wp_strip_all_tags($title),
            'post_type' => 'testimonial'
        );

        if ($test->status == 'approved')
            $post['post_status'] = 'publish';
        else
            $post['post_status'] = 'draft';

        $newid = wp_insert_post($post);

        $newOrder[(int) $test->order - 1] = $newid;

        if (0 != $newid) {
            if ($test->title != $title)
                update_post_meta($newid, 'original-title', $test->title);
            if (0 != $test->category)
                wp_set_object_terms($newid, (int) $categories[$test->category], 'testimonial_category');
            if ('yes' == $test->featured)
                update_post_meta($newid, '_fvt_featured', '1');

            $aConvertedTests = get_option("_fvt_converted_testimonials");
            if (!$aConvertedTests)
                $aConvertedTests = array();
            $aConvertedTests[(int) $test->id] = (int) $newid;
            update_option("_fvt_converted_testimonials", $aConvertedTests);

            // convert here also images
            $images = $wpdb->get_results("SELECT * FROM `wp_fpt_images` WHERE `testimonial`=" . $test->id);
            if ($images) {
                $i = 1;
                $aImages = array();
                foreach ($images as $image) {
                    $aImages[1][$image->type]['path'] = $image->path;
                    $aImages[1][$image->type]['width'] = $image->width;
                    $aImages[1][$image->type]['height'] = $image->height;
                }
                update_post_meta($newid, '_fvt_images', $aImages, true);
            }
        }
    }
    $aOrder[0] = $newOrder;

    update_option('_fvt_order', $aOrder);

    return true;
}

function convert_old_shortcode($old) {

    $shortcode = trim($old);
    $shortcode = str_ireplace('Testimonials:', 'testimonials', $shortcode);
    $shortcode = preg_replace('/-img ([a-z]+)/', ' image="$1"', $shortcode);
    $shortcode = preg_replace('/-cat\s?([\d,]*)/', ' category="$1"', $shortcode);
    $shortcode = preg_replace('/([\d]+)C/', ' category="$1"', $shortcode);

    $shortcode = preg_replace('/([\d]+)F/', ' show="featured"  limit="$1"', $shortcode);
    $shortcode = preg_replace('/-f\s?([\d,]+)/', ' show="featured"  limit="$1"', $shortcode);

    $shortcode = preg_replace('/-c\s?([\d,]+)/', ' limit="$1"', $shortcode);
    $shortcode = preg_replace('/\s*-f\s*/', ' show="featured"', $shortcode);
    $shortcode = preg_replace('/-t\s?(\d+)/', ' template="$1"', $shortcode);
    $shortcode = preg_replace('/-i\s?([\d,]*)/', ' include="$1"', $shortcode);
    $shortcode = preg_replace('/excerpt/', ' length="excerpt" ', $shortcode);
    $shortcode = preg_replace('/\s*[a|A]ll\s*/', ' show="all" ', $shortcode);
    $shortcode = preg_replace('/-e\s?([\d,]*)/', ' show="all" exclude="$1"', $shortcode);

    // tu este treba prehodit idcka kategoriam a testimonialom (-cat a -i)
    $categories = get_option("_fvt_converted_categories");
    preg_match('/category="([\d,]*)"/', $shortcode, $matches);
    $old_cats = explode(',', $matches[1]);
    $newcats = array();
    foreach ($old_cats as $old_cat)
        if (($old_cat) && ($categories[$old_cat]))
            $newcats[] = $categories[$old_cat];
    $replace = 'category="' . implode(',', $newcats) . '"';
    $shortcode = preg_replace('/(category="[\d,]*")/', $replace, $shortcode);
    $tests = get_option("_fvt_converted_testimonials");

    // replace include tstimonials ids
    preg_match('/include="([\d,]*)"/', $shortcode, $matches);
    $old_tests = explode(',', $matches[1]);
    $newtests = array();
    foreach ($old_tests as $old_test)
        if (($old_test) && ($tests[$old_test]))
            $newtests[] = $tests[$old_test];
    $replace = 'include="' . implode(',', $newtests) . '"';
    $shortcode = preg_replace('/(include="[\d,]*")/', $replace, $shortcode);

    // replace exclude tstimonials ids
    preg_match('/exclude="([\d,]*)"/', $shortcode, $matches);
    $old_tests = explode(',', $matches[1]);
    $newtests = array();
    foreach ($old_tests as $old_test)
        if (($old_test) && ($tests[$old_test]))
            $newtests[] = $tests[$old_test];
    $replace = 'exclude="' . implode(',', $newtests) . '"';
    $shortcode = preg_replace('/(exclude="[\d,]*")/', $replace, $shortcode);

    return $shortcode;
}

function fv_testimonials_ajax_convert_shortcodes_posts() {
    global $table_prefix;
    global $wpdb;

    $testimonials = $wpdb->get_results("SELECT * FROM `" . $table_prefix . "posts` WHERE `post_content` LIKE '%\[Testimonials:%' AND `post_status` != 'inherit'");
    $output = "";
    foreach ($testimonials as $testimonial) {
        //parse the testimonial shortcode here, replace it with new version
        $output .= "<br />" . $testimonial->ID . "<br />";
        preg_match_all('/(\[[T|t]estimonials:[\S\s]*?\])/', $testimonial->post_content, $matches);
        foreach ($matches[1] as $shortcode) {
            $output .= $shortcode;
            $shortcode_new = convert_old_shortcode($shortcode);
            $output .= " ---> " . $shortcode_new . "<br />";
            // replace only shortcodes in post content ... should we do also excerpts? .. no there should not be any shortcodes probably
            $query = "UPDATE `" . $table_prefix . "posts` SET `post_content` = replace(`post_content`, '" . $shortcode . "', '" . $shortcode_new . "')";
            $wpdb->query($query);
        }
    }
    return true;
//   die();
}

function fv_testimonials_ajax_convert_shortcodes_theme() {

    global $wpdb;

    $url = get_bloginfo("template_url");
    $temp = explode("wp-content/themes/", $url);
    $active_theme_name = $temp[1];

    $theme = get_theme_root() . '/' . $active_theme_name;
    $output = "";
    if ($handle = opendir($theme)) {
        while (false !== ($entry = readdir($handle))) {
            $file = $theme . '/' . $entry;
            if (is_file($file)) {
                $filecontent = file_get_contents($file);
                preg_match_all('/(\[[T|t]estimonials:[\S\s]*?\])/', $filecontent, $matches);
                if ($matches[1]) {
                    foreach ($matches[1] as $old_shortcode) {
                        $new_shortcode = convert_old_shortcode($old_shortcode);
                        $filecontent = str_replace($old_shortcode, $new_shortcode, $filecontent);
                    }
                    $output .= " <br /> ";
                    if (file_put_contents($file, $filecontent) === false)
                        $output .= "Error writing into file " . $file . "<br />";
                    $output .= "Theme file " . $file . " was updated. ";
                }
            }
        }
        closedir($handle);
    }

    return true;
    // die();
}

function replace_shortcode_in_array($array) {

    foreach ($array as $item => $value) {
        if (is_array($value))
            $array[$item] = replace_shortcode_in_array($value);
        else {
            preg_match_all('/(\[[T|t]estimonials:[\S\s]*?\])/', $value, $matches);
            foreach ($matches[1] as $old_shortcode) {
                $new_shortcode = convert_old_shortcode($old_shortcode);
                $value = str_replace($old_shortcode, $new_shortcode, $value);
                $array[$item] = $value;
                // var_dump("SHFOUND >>>");var_dump($new_shortcode); var_dump("<<< FOUND");
            }
        }
    }
    return $array;
}

/*
function fv_testimonials_ajax_convert_shortcodes_db() {
    global $table_prefix;

    global $wpdb;
    $tables = $wpdb->get_results("SHOW TABLES");
    $output = "";
    foreach ($tables as $table) {
        if ($table->Tables_in_detoute_ccc == $table_prefix . 'posts')
            continue; // skip posts and pages, we have separate routine for that
        if (in_array($table->Tables_in_detoute_ccc, array($table_prefix . 'users', $table_prefix . 'usermeta', $table_prefix . 'terms', $table_prefix . 'term_relationships', $table_prefix . 'term_taxonomy', $table_prefix . 'links', $table_prefix . 'users', $table_prefix . 'comments', $table_prefix . 'commentmeta')))
            continue; // skip also other wp default tables where the shortcode certainly should not be - basically search only in options, postmeta and other tables created by other plugins
        $columns = $wpdb->get_results("SHOW COLUMNS IN " . $table->Tables_in_detoute_ccc);
        $query = "";
        foreach ($columns as $column) {
            if ($query)
                $query .= " OR ";
            $query .= "`" . $column->Field . "` LIKE '%[Testimonials:%' ";
        }
        $found_in_table = $wpdb->get_results("SELECT * FROM " . $table->Tables_in_detoute_ccc . " WHERE " . $query);
        if ($found_in_table) {
            $output .= $table->Tables_in_detoute_ccc . " ";  // here we have something with testimonials! check it out!!!
            foreach ($found_in_table as $row) {
                foreach ($row as $option => $value) {
                    if (stripos($value, '[Testimonials:') !== false) {
                        if (is_serialized($value)) {
                            //var_dump(" serialized ");
                            //var_dump($value);
                            $value_old = unserialize($value);
                            //var_dump($value_old);
                            $value_new = replace_shortcode_in_array($value_old);
                            $option_new = serialize($value_new);

                            if (strlen($option_new) >= strlen($value)) {
                                $option_new = mysql_real_escape_string($option_new);
                                $value = mysql_real_escape_string($value);
                                // this replaces the whole cell
                                $query = "UPDATE " . $table->Tables_in_detoute_ccc . " SET `" . $option . "`='" . $option_new . "' WHERE `" . $option . "`='" . $value . "'";
                                //var_dump($query);
                                //$wpdb->query($query);
                            }
                        } else {
                            //var_dump(" not serialized "); 
                            preg_match_all('/(\[[T|t]estimonials:[\S\s]*?\])/', $value, $matches);
                            $value_new = $value;
                            foreach ($matches[1] as $old_shortcode) {
                                $new_shortcode = convert_old_shortcode($old_shortcode);
                                //$value_new = str_replace($old_shortcode, $new_shortcode, $value_new);
                                // this replaces just the shortcode itself
                                $query = "UPDATE `" . $table->Tables_in_detoute_ccc . "` SET `" . $option . "` = replace(`" . $option . "`, '" . $old_shortcode . "', '" . $new_shortcode . "')";
                                //var_dump($query);
                                //$wpdb->query($query);   // toto alebo to pod tym
                            }
                        }
                    }
                }
            }
        }
    }
    echo '<span id="found_db">' . $output . '</span>';
    die();
}
*/

// convert db
function fv_testimonials_ajax_convert_shortcodes_db() {
    global $wpdb;
    $output = "";
    // get list of all tebales to be checked
    $tables = fv_testimonials_convert_db_list_of_tables();
    $tables_to_convert = array();

    if (count($tables) == 0)
        return true;

    foreach ($tables as $key => $table) {
//        echo "$key > $table";
        if ($result = fv_testimonials_convert_db_list_of_fields($table)) {
            $tables_to_convert[$key] = $result;
            $tables_to_convert[$key]['table'] = $table;
        }
    }
    
    //if no tables in db to convert exit
    if (count($tables_to_convert) < 1)
        return true;

    unset($tables);
    
    // start convertion
    foreach ($tables_to_convert as $table) {
        // find records where old shortcode ('%\[Testimonials:%') is used and replace it ('%\[Testimonials:%');
        if(isset($query)) unset($query);
        foreach ($table['where'] as $where) {
            if ($query)
                $query .= " OR ";
            $query .= "`" . $where . "` LIKE '%[Testimonials:%' ";
        }
        
        
        $select = "`".implode("`,`", $table['where']) . "`,`" . implode("`,`", $table['pri'])."`";
        
        //echo "SELECT " . $select . " FROM `" . $table['table'] . "` WHERE " . $query."<hr />";
        
        $found_in_table = $wpdb->get_results("SELECT " . $select . " FROM `" . $table['table'] . "` WHERE " . $query);
        
        if ($found_in_table) {
            $output .= $table['table'];
            // *************************
            foreach ($found_in_table as $row) {
                foreach ($row as $option => $value) {
                    if (stripos($value, '[Testimonials:') !== false) {
                        if (is_serialized($value)) {
                            //var_dump(" serialized ");
                            //var_dump($value);
                            $value_old = unserialize($value);
                            //var_dump($value_old);
                            $value_new = replace_shortcode_in_array($value_old);
                            $option_new = serialize($value_new);

                            if (strlen($option_new) >= strlen($value)) {
                                $option_new = mysql_real_escape_string($option_new);
                                $value = mysql_real_escape_string($value);
                                // this replaces the whole cell
                                $query = "UPDATE " . $table['table'] . " SET `" . $option . "`='" . $option_new . "' WHERE `" . $option . "`='" . $value . "'";
                                //echo "<b>$query</b><hr />";
                                $wpdb->query($query);
                            }
                        } else {
                            //var_dump(" not serialized "); 
                            preg_match_all('/(\[[T|t]estimonials:[\S\s]*?\])/', $value, $matches);
                            $value_new = $value;
                            foreach ($matches[1] as $old_shortcode) {
                                $new_shortcode = convert_old_shortcode($old_shortcode);
                                //$value_new = str_replace($old_shortcode, $new_shortcode, $value_new);
                                // this replaces just the shortcode itself
                                $query = "UPDATE `" . $table['table'] . "` SET `" . $option . "` = replace(`" . $option . "`, '" . $old_shortcode . "', '" . $new_shortcode . "')";
                                //echo "<b>$query</b><hr />";
                                $wpdb->query($query);
                            }
                        }
                    }
                }
            }
            // *************************
        }
    }
//    if(!empty($output)) echo '<span id="found_db">' . $output . '</span>';
//    die();
/*
    echo "<pre>";
    print_r($tables_to_convert);
    echo "</pre>";
*/
}

function fv_testimonials_convert_db_list_of_tables() {
    global $wpdb;

    $skip_tables = array($wpdb->posts, $wpdb->links, $wpdb->terms, $wpdb->term_relationships, $wpdb->term_taxonomy, $wpdb->users, $wpdb->usermeta, $wpdb->options, $wpdb->comments, $wpdb->commentmeta);

    $result = $wpdb->get_results('SHOW FULL TABLES', ARRAY_N);
    $tables = array();
    foreach ($result as $table) {
        if (!in_array($table[0], $skip_tables))
            $tables[] = $table[0];
    }
    return $tables;
}

function fv_testimonials_convert_db_list_of_fields($table) {
    global $wpdb;

    $search = array();
    $pri = array();
    $scan_types = array('text', 'char', 'blob');

    $result = $wpdb->get_results('SHOW COLUMNS FROM ' . $table);

    foreach ($result as $column) {
        foreach ($scan_types as $scan_type) {
            if (preg_match('/' . $scan_type . '/i', $column->Type)) {
                // column is 'text','varchar','char','blob'
                $search[] = $column->Field;
                break;
            }
        }

        if (preg_match('/pri/i', $column->Key)) {
            $pri[] = $column->Field;
        }
    }

    // if table has text columns and primary key column
    if (count($search) > 0 AND count($pri) > 0) {
        $return['pri'] = $pri;
        $return['where'] = $search;
        return $return;
    }
    return false;
}

?>
<?php
/*
Plugin Name: Custom Form
Description: Custom Form Plugin.
Version: 1.0
Author: Nutthaon
*/

// สร้างตารางในฐานข้อมูลเมื่อเปิดใช้งานปลั๊กอิน
function custom_form_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_form_data';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        firstname varchar(100) NOT NULL,
        lastname varchar(100) NOT NULL,
        phone varchar(20) NOT NULL,
        email varchar(100) NOT NULL,
        subject varchar(200) NOT NULL,
        message text NOT NULL,
        timestamp datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'custom_form_install');

// สร้างแบบฟอร์ม HTML และตัวอย่างการแสดงผลข้อมูล
function custom_form_shortcode() {
    ob_start();
    ?>

    <style>
        input[type=text],input[type=email],.site textarea{
            border: 2px solid #c5c5c5;
            border-radius: 5px;
        }
        #custom-form
        #custom-form {
            margin-bottom: 20px;
        }

        #custom-form label {
            display: block;
            margin-bottom: 5px;
        }

        #custom-form input,
        #custom-form textarea {
            width: 100%;
            padding: 5px;
            margin-bottom: 10px;
        }

        #custom-form textarea{
            height: 100px;
        }

        #custom-form input[type="submit"] {
            background-color: red;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
        }

        #custom-form input[type="submit"]:hover {
            background-color: #e74d4d;
        }

        #custom-form-response {
            margin-top: 20px;
            font-weight: bold;
        }
        .fullcolumn{
            width: 100%!important;
            clear: both!important;
            display: block;
        }
        .halfcolumnleft {
            width: 48%!important;
            float: left;
            clear: none;
            display: block;
        }
        .halfcolumnright {
            width: 48%!important;
            float: right;
            clear: none;
            display: block;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

    <form id="custom-form" method="post" class="row">
        <div class="fullcolumn">
            <div class="halfcolumnleft">
                <label for="firstname">ชื่อ</label>
                <input type="text" name="firstname" placeholder="ชื่อ" required>
            </div>
            <div class="halfcolumnright">
                <label for="lastname">นามสกุล</label>
                <input type="text" name="lastname" placeholder="นามสกุล" required>
            </div>
        </div>
        <div class="fullcolumn">
            <div class="halfcolumnleft">
                <label for="phone">เบอร์โทร</label>
                <input type="text" name="phone" placeholder="เบอร์โทรศัพท์" required>
            </div>
            <div class="halfcolumnright">
                <label for="email">อีเมล</label>
                <input type="email" name="email" placeholder="อีเมล" required>
            </div>
        </div>
        <div class="fullcolumn">
            <label for="subject">หัวข้อ</label>
            <input type="text" name="subject" placeholder="หัวข้อ" required>
        </div>
        <div class="fullcolumn">
            <label for="message">ข้อความ</label>
            <textarea name="message" placeholder="โปรดระบุข้อความ..." required></textarea>
        </div>
        <div class="fullcolumn">
            <div class="halfcolumnright">
                <input type="submit" value="ส่งข้อความ">
            </div>
        </div>
    </form>

    <div id="custom-form-response"></div>

    <script>
        jQuery(document).ready(function($) {
            $('#custom-form').submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize();

                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        'action': 'custom_form_submit',
                        'data': formData
                    },
                    success: function(response) {
                        $('#custom-form-response').html(response);
                    }
                });
            });
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('custom-form', 'custom_form_shortcode');

// บันทึกข้อมูลฟอร์มที่ส่งมาในฐานข้อมูล
function custom_form_submit() {
    if (isset($_POST['data'])) {
        parse_str($_POST['data'], $form_data);

        global $wpdb;
        $table_name = $wpdb->prefix . 'custom_form_data';

        $wpdb->insert(
            $table_name,
            array(
                'firstname' => $form_data['firstname'],
                'lastname' => $form_data['lastname'],
                'phone' => $form_data['phone'],
                'email' => $form_data['email'],
                'subject' => $form_data['subject'],
                'message' => $form_data['message']
            )
        );

        echo 'ส่งข้อมูลและบันทึกเรียบร้อยแล้ว!';
    }
    wp_die();
}
add_action('wp_ajax_custom_form_submit', 'custom_form_submit');
add_action('wp_ajax_nopriv_custom_form_submit', 'custom_form_submit');

// แสดงผลข้อมูลที่บันทึกไว้
function custom_form_display_data() {
    ob_start();

    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_form_data';

    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");

    if (!empty($results)) {
        echo '<h2>รายการข้อมูลที่บันทึกแล้ว:</h2>';
        echo '<table id="custom-form-data">';
        echo '<thead><tr><th>ชื่อ</th><th>นามสกุล</th><th>เบอร์โทร</th><th>อีเมล</th><th>หัวข้อ</th><th style="min-width:50px">ข้อความ</th><th style="min-width:50px">เวลา</th></tr></thead>';
        echo '<tbody>';
        foreach ($results as $result) {
            echo '<tr>';
            echo '<td>' . $result->firstname . '</td>';
            echo '<td>' . $result->lastname . '</td>';
            echo '<td>' . $result->phone . '</td>';
            echo '<td>' . $result->email . '</td>';
            echo '<td>' . $result->subject . '</td>';
            echo '<td>' . $result->message . '</td>';
            echo '<td>' . $result->timestamp . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo 'ยังไม่มีข้อมูลที่บันทึก';
    }


    return ob_get_clean();
}
add_shortcode('custom-form-data', 'custom_form_display_data');

function custom_form_menu_page() {
    add_menu_page(
        'Custom Form',       // Page title
        'Custom Form',       // Menu title
        'manage_options', // Capability required to access the menu page
        'custom_form',       // Menu slug
        'custom_form_plugin_page', // Callback function to display the menu page
        'dashicons-format-aside', // Menu icon
        6               // Menu position
    );
}
add_action('admin_menu', 'custom_form_menu_page');

function custom_form_enqueue_scripts() {
    wp_enqueue_style( 'style', plugin_dir_url(__FILE__) . 'style.css' );
    wp_enqueue_script('custom-form-ajax', plugin_dir_url(__FILE__) . 'custom-form-ajax.js', array('jquery'), '1.0', true);
    wp_localize_script('custom-form-ajax', 'customFormAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
}
add_action('admin_enqueue_scripts', 'custom_form_enqueue_scripts');

function custom_form_plugin_page() {
    echo '<div id="custom-form-data-container">';
    echo custom_form_display_data();
    echo '</div>';
}

function update_custom_form_data() {
    ob_start();

    global $wpdb;
    $table_name = $wpdb->prefix . 'custom_form_data';

    $results = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC");

    if (!empty($results)) {
        echo '<h2>รายการข้อมูลที่บันทึกแล้ว:</h2>';
        echo '<table id="custom-form-data">';
        echo '<thead><tr><th>ชื่อ</th><th>นามสกุล</th><th>เบอร์โทร</th><th>อีเมล</th><th>หัวข้อ</th><th style="min-width:50px">ข้อความ</th><th style="min-width:50px">เวลา</th></tr></thead>';
        echo '<tbody>';
        foreach ($results as $result) {
            echo '<tr>';
            echo '<td>' . $result->firstname . '</td>';
            echo '<td>' . $result->lastname . '</td>';
            echo '<td>' . $result->phone . '</td>';
            echo '<td>' . $result->email . '</td>';
            echo '<td>' . $result->subject . '</td>';
            echo '<td>' . $result->message . '</td>';
            echo '<td>' . $result->timestamp . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo 'ยังไม่มีข้อมูลที่บันทึก';
    }

    $content = ob_get_clean();
    echo $content;
    die();
}
add_action('wp_ajax_update_custom_form_data', 'update_custom_form_data');
add_action('wp_ajax_nopriv_update_custom_form_data', 'update_custom_form_data');

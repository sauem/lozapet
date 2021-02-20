<?php
/**
 * Cấu hình cơ bản cho WordPress
 *
 * Trong quá trình cài đặt, file "wp-config.php" sẽ được tạo dựa trên nội dung
 * mẫu của file này. Bạn không bắt buộc phải sử dụng giao diện web để cài đặt,
 * chỉ cần lưu file này lại với tên "wp-config.php" và điền các thông tin cần thiết.
 *
 * File này chứa các thiết lập sau:
 *
 * * Thiết lập MySQL
 * * Các khóa bí mật
 * * Tiền tố cho các bảng database
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** Thiết lập MySQL - Bạn có thể lấy các thông tin này từ host/server ** //
/** Tên database MySQL */
define( 'DB_NAME', 'lozapet2020' );

/** Username của database */
define( 'DB_USER', 'sauem' );

/** Mật khẩu của database */
define( 'DB_PASSWORD', '123' );

/** Hostname của database */
define( 'DB_HOST', 'localhost' );

/** Database charset sử dụng để tạo bảng database. */
define( 'DB_CHARSET', 'utf8mb4' );

/** Kiểu database collate. Đừng thay đổi nếu không hiểu rõ. */
define('DB_COLLATE', '');

/**#@+
 * Khóa xác thực và salt.
 *
 * Thay đổi các giá trị dưới đây thành các khóa không trùng nhau!
 * Bạn có thể tạo ra các khóa này bằng công cụ
 * {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * Bạn có thể thay đổi chúng bất cứ lúc nào để vô hiệu hóa tất cả
 * các cookie hiện có. Điều này sẽ buộc tất cả người dùng phải đăng nhập lại.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'B~kEW|KCn47KvIb$@i8@olEku~^A pJG7.cyPwq*s[>U!vp}](a_MKjs^P0viTG*' );
define( 'SECURE_AUTH_KEY',  '!r~r|7JA55I=<3pzhOccK*SagKGO!eTlFts3>!p0UoGe,^~@t@5$Q7+p^rCP*04T' );
define( 'LOGGED_IN_KEY',    '$vw3Pa*H#eb~-4X|)Q Irct{l#E&,xY]E:laP?Vya{$jzD!)V+%:M6Ynuf[QOliI' );
define( 'NONCE_KEY',        '8`G_z_3v}HDy_&|NXW?_9&g&XZRtX|kTSHaDQY},))kqS [wA>E]!@s[qc!zRl_F' );
define( 'AUTH_SALT',        'kTRI2Ihc#{XCG-X>Qtksu[D7VA?fJhl&-3^?}Qd#idcF.28bxBkl1d?tiNXz)X/I' );
define( 'SECURE_AUTH_SALT', '*W1Pr|(p/I|_;:de!IhKzYe8l.ew,dA/2d|ZnMEf `.ryje%+r`<8/6~ AnCV@j/' );
define( 'LOGGED_IN_SALT',   'dkZ=Dv~oIcm6WH5aS5L4he?fgK1E@R)>.2:.9k$+5((z4[Cv1xvL1<I$IfgVlC+<' );
define( 'NONCE_SALT',       'Tgr]/%YVW-aS0D;kwl3b& /G~z}5`2clqJMa]#=IP,]in7?(9@Fo8tN^(M-X%#_7' );

/**#@-*/

/**
 * Tiền tố cho bảng database.
 *
 * Đặt tiền tố cho bảng giúp bạn có thể cài nhiều site WordPress vào cùng một database.
 * Chỉ sử dụng số, ký tự và dấu gạch dưới!
 */
$table_prefix = 'wp_';

/**
 * Dành cho developer: Chế độ debug.
 *
 * Thay đổi hằng số này thành true sẽ làm hiện lên các thông báo trong quá trình phát triển.
 * Chúng tôi khuyến cáo các developer sử dụng WP_DEBUG trong quá trình phát triển plugin và theme.
 *
 * Để có thông tin về các hằng số khác có thể sử dụng khi debug, hãy xem tại Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', TRUE);

/* Đó là tất cả thiết lập, ngưng sửa từ phần này trở xuống. Chúc bạn viết blog vui vẻ. */

/** Đường dẫn tuyệt đối đến thư mục cài đặt WordPress. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Thiết lập biến và include file. */
require_once(ABSPATH . 'wp-settings.php');

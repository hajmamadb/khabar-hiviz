<?php
/**
 * فهرست فیدهای پیش‌فرض پلاگین خبریاب.
 *
 * هر مورد:
 *  - name: نام فید (موضوع + نام منبع در پرانتز)
 *  - url: آدرس فید
 *  - topic: نام دسته‌ی موضوعی (مثل «اخبار سیاسی»); هنگام فعال‌سازی دسته ساخته
 *    یا پیدا شده و شناسه‌اش به فید داده می‌شود.
 *  - max_items: (اختیاری) حداکثر خبر در هر چک.
 *
 * این فهرست هنگام فعال‌سازی با KHV_Settings::seed_default_feeds() به تنظیمات
 * اضافه می‌شود (فقط آدرس‌هایی که قبلا‌ وجود ندارند) تا بعد از به‌روزرسانی،
 * فیدهای تازه‌افزوده به‌صورت خودکار در دسترس شوند.
 *
 * بخش آخر («منابع تکمیلی») از شبکه‌ی توسعه قابل‌بررسی نبود و آدرس‌های
 * استاندارد هر سایت داده شده است؛ در صورت فعال نبودن، از پنل غیرفعال کنید.
 *
 * @package KhabarHiviz
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function khv_default_feeds() {
	$feeds = array(

		// --- خبرآنلاین ---
		array( 'name' => 'اخبار سیاسی (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/1', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'اخبار اقتصادی (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/2', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'اخبار فرهنگی و هنری (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/3', 'topic' => 'اخبار فرهنگی و هنری' ),
		array( 'name' => 'اخبار اجتماعی (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/4', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'اخبار بین‌الملل (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/5', 'topic' => 'اخبار بین‌الملل' ),
		array( 'name' => 'اخبار ورزشی (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/6', 'topic' => 'اخبار ورزشی' ),
		array( 'name' => 'اخبار دانش و فناوری (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/7', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'رهبری (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/14', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'دولت (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/18', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'مجلس (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/19', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'نظامی (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/16', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'اقتصاد کلان (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/20', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'بازار مالی (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/24', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'مسکن (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/21', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'انرژی (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/26', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'کشاورزی (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/29', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'سینما (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/32', 'topic' => 'اخبار فرهنگی و هنری' ),
		array( 'name' => 'ادبیات (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/35', 'topic' => 'اخبار فرهنگی و هنری' ),
		array( 'name' => 'موسیقی (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/36', 'topic' => 'اخبار فرهنگی و هنری' ),
		array( 'name' => 'تئاتر (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/39', 'topic' => 'اخبار فرهنگی و هنری' ),
		array( 'name' => 'کتاب (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/41', 'topic' => 'اخبار فرهنگی و هنری' ),
		array( 'name' => 'آموزش (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/43', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'سلامت (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/46', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'محیط زیست (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/44', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'حوادث (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/51', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'قضایی (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/50', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'آمریکا (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/56', 'topic' => 'اخبار بین‌الملل' ),
		array( 'name' => 'خاورمیانه (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/58', 'topic' => 'اخبار بین‌الملل' ),
		array( 'name' => 'اروپا (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/1214', 'topic' => 'اخبار بین‌الملل' ),
		array( 'name' => 'چین (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/1346', 'topic' => 'اخبار بین‌الملل' ),
		array( 'name' => 'فوتبال ملی (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/64', 'topic' => 'فوتبال' ),
		array( 'name' => 'لیگ برتر (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/71', 'topic' => 'فوتبال' ),
		array( 'name' => 'فوتبال جهان (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/65', 'topic' => 'فوتبال' ),
		array( 'name' => 'والیبال (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/66', 'topic' => 'اخبار ورزشی' ),
		array( 'name' => 'بسکتبال (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/67', 'topic' => 'اخبار ورزشی' ),
		array( 'name' => 'فناوری (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/74', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'پزشکی (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/73', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'اینترنت (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/77', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'هوش مصنوعی (خبرآنلاین)', 'url' => 'https://www.khabaronline.ir/rss/tp/1455', 'topic' => 'اخبار دانش و فناوری' ),

		// --- ایرنا ---
		array( 'name' => 'سیاست (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/5', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'رهبر انقلاب (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/6', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'دولت (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/7', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'مجلس (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/8', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'دفاعی و امنیتی (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/9', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'احزاب و تشکل‌ها (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/13', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'اقتصاد (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/20', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'نفت و انرژی (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/21', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'بازرگانی (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/22', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'صنعت و معدن (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/23', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'کشاورزی (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/24', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'راه و مسکن (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/25', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'بانک و بیمه (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/26', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'اقتصاد کلان (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/27', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'جامعه (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/32', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'رفاه و تأمین اجتماعی (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/33', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'حقوقی و قضایی (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/34', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'زنان و خانواده (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/38', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'محیط زیست (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/40', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'اروپا (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/2', 'topic' => 'اخبار بین‌الملل' ),
		array( 'name' => 'آسیای شرقی (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/3', 'topic' => 'اخبار بین‌الملل' ),
		array( 'name' => 'ورزش (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/14', 'topic' => 'اخبار ورزشی' ),
		array( 'name' => 'کشتی و وزنه‌برداری (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/16', 'topic' => 'اخبار ورزشی' ),
		array( 'name' => 'ورزش‌های رزمی (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/17', 'topic' => 'اخبار ورزشی' ),
		array( 'name' => 'توپ و تور (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/18', 'topic' => 'اخبار ورزشی' ),
		array( 'name' => 'فوتبال (ایرنا)', 'url' => 'https://www.irna.ir/rss/tp/15', 'topic' => 'فوتبال' ),

		// --- ایسنا ---
		array( 'name' => 'سیاسی (ایسنا)', 'url' => 'https://www.isna.ir/rss/tp/14', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'اقتصادی (ایسنا)', 'url' => 'https://www.isna.ir/rss/tp/34', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'فرهنگی و هنری (ایسنا)', 'url' => 'https://www.isna.ir/rss/tp/20', 'topic' => 'اخبار فرهنگی و هنری' ),
		array( 'name' => 'فرهنگ حماسه (ایسنا)', 'url' => 'https://www.isna.ir/rss/tp/31', 'topic' => 'اخبار فرهنگی و هنری' ),
		array( 'name' => 'اجتماعی (ایسنا)', 'url' => 'https://www.isna.ir/rss/tp/9', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'جهان (ایسنا)', 'url' => 'https://www.isna.ir/rss/tp/17', 'topic' => 'اخبار بین‌الملل' ),
		array( 'name' => 'ورزشی (ایسنا)', 'url' => 'https://www.isna.ir/rss/tp/24', 'topic' => 'اخبار ورزشی' ),
		array( 'name' => 'علمی و دانشگاهی (ایسنا)', 'url' => 'https://www.isna.ir/rss/tp/5', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'پژوهش (ایسنا)', 'url' => 'https://www.isna.ir/rss/tp/38', 'topic' => 'اخبار دانش و فناوری' ),

		// --- خبرگزاری مهر ---
		array( 'name' => 'سیاست (مهر)', 'url' => 'https://www.mehrnews.com/rss/tp/7', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'مجلس (مهر)', 'url' => 'https://www.mehrnews.com/rss/tp/38', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'امنیتی و دفاعی (مهر)', 'url' => 'https://www.mehrnews.com/rss/tp/39', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'سیاست خارجی (مهر)', 'url' => 'https://www.mehrnews.com/rss/tp/40', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'اقتصاد (مهر)', 'url' => 'https://www.mehrnews.com/rss/tp/25', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'هنر (مهر)', 'url' => 'https://www.mehrnews.com/rss/tp/1', 'topic' => 'اخبار فرهنگی و هنری' ),
		array( 'name' => 'فرهنگ و ادب (مهر)', 'url' => 'https://www.mehrnews.com/rss/tp/2', 'topic' => 'اخبار فرهنگی و هنری' ),
		array( 'name' => 'سینمای ایران (مهر)', 'url' => 'https://www.mehrnews.com/rss/tp/29', 'topic' => 'اخبار فرهنگی و هنری' ),
		array( 'name' => 'تئاتر (مهر)', 'url' => 'https://www.mehrnews.com/rss/tp/32', 'topic' => 'اخبار فرهنگی و هنری' ),
		array( 'name' => 'جامعه (مهر)', 'url' => 'https://www.mehrnews.com/rss/tp/6', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'دین، حوزه، اندیشه (مهر)', 'url' => 'https://www.mehrnews.com/rss/tp/3', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'بهداشت (مهر)', 'url' => 'https://www.mehrnews.com/rss/tp/36', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'میراث فرهنگی و گردشگری (مهر)', 'url' => 'https://www.mehrnews.com/rss/tp/37', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'بین‌الملل (مهر)', 'url' => 'https://www.mehrnews.com/rss/tp/8', 'topic' => 'اخبار بین‌الملل' ),
		array( 'name' => 'ورزش (مهر)', 'url' => 'https://www.mehrnews.com/rss/tp/9', 'topic' => 'اخبار ورزشی' ),
		array( 'name' => 'دانشگاه و فناوری (مهر)', 'url' => 'https://www.mehrnews.com/rss/tp/5', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'فوتبال ایران (مهر)', 'url' => 'https://www.mehrnews.com/rss/tp/24', 'topic' => 'فوتبال' ),
		array( 'name' => 'فوتبال جهان (مهر)', 'url' => 'https://www.mehrnews.com/rss/tp/30', 'topic' => 'فوتبال' ),

		// --- خبرورزشی ---
		array( 'name' => 'فوتبال (خبرورزشی)', 'url' => 'https://www.khabarvarzeshi.com/rss/tp/1', 'topic' => 'فوتبال' ),
		array( 'name' => 'منهای فوتبال (خبرورزشی)', 'url' => 'https://www.khabarvarzeshi.com/rss/tp/2', 'topic' => 'اخبار ورزشی' ),
		array( 'name' => 'ورزش زنان (خبرورزشی)', 'url' => 'https://www.khabarvarzeshi.com/rss/tp/3', 'topic' => 'اخبار ورزشی' ),

		// --- تبیان ---
		array( 'name' => 'تبیان (عمومی)', 'url' => 'https://www.tebyan.net/rss', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'ورزشی (تبیان)', 'url' => 'https://www.tebyan.net/rss/tp/36', 'topic' => 'اخبار ورزشی' ),

		// --- آنا ---
		array( 'name' => 'آنا (دانشگاهی، عمومی)', 'url' => 'https://www.ana.ir/fa/rss/allnews', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'فناوری و امنیت سایبری (آنا)', 'url' => 'https://www.ana.ir/fa/rss/1', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'علم و فناوری (آنا)', 'url' => 'https://www.ana.ir/fa/rss/2', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'پارک فناوری و صنعت (آنا)', 'url' => 'https://www.ana.ir/fa/rss/21', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'اختراعات و پژوهش (آنا)', 'url' => 'https://www.ana.ir/fa/rss/28', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'ثبت اختراع (آنا)', 'url' => 'https://www.ana.ir/fa/rss/43', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'علم و سلامت (آنا)', 'url' => 'https://www.ana.ir/fa/rss/62', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'هوش مصنوعی و نرم‌افزار (آنا)', 'url' => 'https://www.ana.ir/fa/rss/63', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'علوم پزشکی (آنا)', 'url' => 'https://www.ana.ir/fa/rss/64', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'اقتصاد و انرژی (آنا)', 'url' => 'https://www.ana.ir/fa/rss/7', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'مالی و بانکی (آنا)', 'url' => 'https://www.ana.ir/fa/rss/13', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'کشاورزی (آنا)', 'url' => 'https://www.ana.ir/fa/rss/16', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'امنیت غذایی (آنا)', 'url' => 'https://www.ana.ir/fa/rss/25', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'آموزش و پرورش (آنا)', 'url' => 'https://www.ana.ir/fa/rss/3', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'دانشگاه (آنا)', 'url' => 'https://www.ana.ir/fa/rss/17', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'دانشکده‌ها (آنا)', 'url' => 'https://www.ana.ir/fa/rss/20', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'دانشگاه فارس (آنا)', 'url' => 'https://www.ana.ir/fa/rss/30', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'آموزش دانشگاهی (آنا)', 'url' => 'https://www.ana.ir/fa/rss/34', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'آموزش عمومی (آنا)', 'url' => 'https://www.ana.ir/fa/rss/38', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'حوادث و امداد (آنا)', 'url' => 'https://www.ana.ir/fa/rss/65', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'فرهنگ و هنر (آنا)', 'url' => 'https://www.ana.ir/fa/rss/10', 'topic' => 'اخبار فرهنگی و هنری' ),
		array( 'name' => 'فرهنگ عمومی (آنا)', 'url' => 'https://www.ana.ir/fa/rss/26', 'topic' => 'اخبار فرهنگی و هنری' ),
		array( 'name' => 'فرهنگ (آنا)', 'url' => 'https://www.ana.ir/fa/rss/31', 'topic' => 'اخبار فرهنگی و هنری' ),
		array( 'name' => 'سیاست (آنا)', 'url' => 'https://www.ana.ir/fa/rss/18', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'ورزش دانشجویی (آنا)', 'url' => 'https://www.ana.ir/fa/rss/9', 'topic' => 'اخبار ورزشی' ),

		// --- میزان ---
		array( 'name' => 'میزان (عمومی)', 'url' => 'https://www.mizanonline.com/fa/rss/allnews', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'قوه قضاییه (میزان)', 'url' => 'https://www.mizanonline.com/fa/rss/18', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'قضایی (میزان)', 'url' => 'https://www.mizanonline.com/fa/rss/4', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'حقوقی (میزان)', 'url' => 'https://www.mizanonline.com/fa/rss/16', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'حل اختلاف و مالکیت فکری (میزان)', 'url' => 'https://www.mizanonline.com/fa/rss/17', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'بین‌الملل (میزان)', 'url' => 'https://www.mizanonline.com/fa/rss/7', 'topic' => 'اخبار بین‌الملل' ),

		// --- ایران اینترنشنال ---
		array( 'name' => 'ایران اینترنشنال (عمومی)', 'url' => 'https://www.iranintl.com/feed', 'topic' => 'اخبار بین‌الملل' ),

		// --- دنیای اقتصاد ---
		array( 'name' => 'دنیای اقتصاد (عمومی)', 'url' => 'https://www.donya-e-eqtesad.com/rss', 'topic' => 'اخبار اقتصادی' ),

		// --- شرق ---
		array( 'name' => 'شرق (عمومی)', 'url' => 'https://www.sharghdaily.ir/feeds', 'topic' => 'اخبار سیاسی' ),

		// --- دانشجو ---
		array( 'name' => 'دانشجو (عمومی)', 'url' => 'https://snn.ir/fa/rss/allnews', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'دانشجو (استان‌ها و شهرها)', 'url' => 'https://snn.ir/fa/rss/2', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'دانشجو (بین‌الملل)', 'url' => 'https://snn.ir/fa/rss/3', 'topic' => 'اخبار بین‌الملل' ),
		array( 'name' => 'دانشجو (فرهنگی)', 'url' => 'https://snn.ir/fa/rss/4', 'topic' => 'اخبار فرهنگی و هنری' ),
		array( 'name' => 'دانشجو (سیاسی و امنیتی)', 'url' => 'https://snn.ir/fa/rss/6', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'دانشجو (اقتصادی)', 'url' => 'https://snn.ir/fa/rss/7', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'دانشجو (بین‌الملل ۲)', 'url' => 'https://snn.ir/fa/rss/8', 'topic' => 'اخبار بین‌الملل' ),
		array( 'name' => 'دانشجو (اجتماعی و حوادث)', 'url' => 'https://snn.ir/fa/rss/9', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'دانشجو (فوتبال)', 'url' => 'https://snn.ir/fa/rss/10', 'topic' => 'فوتبال' ),
		array( 'name' => 'دانشجو (دانشگاه و پژوهش)', 'url' => 'https://snn.ir/fa/rss/11', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'دانشجو (آموزش)', 'url' => 'https://snn.ir/fa/rss/12', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'دانشجو (استان‌ها)', 'url' => 'https://snn.ir/fa/rss/14', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'دانشجو (رهبری و سیاست)', 'url' => 'https://snn.ir/fa/rss/15', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'دانشجو (علم و سلامت)', 'url' => 'https://snn.ir/fa/rss/16', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'دانشجو (دانشگاه)', 'url' => 'https://snn.ir/fa/rss/17', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'دانشجو (ورزشی و رسانه)', 'url' => 'https://snn.ir/fa/rss/18', 'topic' => 'اخبار ورزشی' ),

		// --- اقتصاد آنلاین ---
		array( 'name' => 'اقتصاد آنلاین (عمومی)', 'url' => 'https://www.eghtesadonline.com/fa/rss/allnews', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'دانش و فناوری (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/1', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'بانکی و مالی (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/3', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'بازار خودرو (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/6', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'سیاست‌گذاری اقتصادی (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/7', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'بورس و سهام (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/9', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'رمزارز (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/10', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'بین‌الملل (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/11', 'topic' => 'اخبار بین‌الملل' ),
		array( 'name' => 'طلا (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/13', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'نفت و بازارهای جهانی (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/14', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'ورزش (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/16', 'topic' => 'اخبار ورزشی' ),
		array( 'name' => 'سلامت (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/17', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'فناوری و هوش مصنوعی (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/18', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'استان‌ها (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/19', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'فرهنگ و محیط زیست (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/20', 'topic' => 'اخبار فرهنگی و هنری' ),
		array( 'name' => 'تجارت الکترونیک (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/22', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'کشاورزی (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/24', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'بانک مرکزی (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/25', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'تجارت خارجی (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/26', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'انرژی (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/27', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'عمران و مسکن (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/28', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'اقتصاد معیشتی (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/29', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'سیاست (اقتصاد آنلاین)', 'url' => 'https://www.eghtesadonline.com/fa/rss/30', 'topic' => 'اخبار سیاسی' ),

		// --- ورزش سه ---
		array( 'name' => 'ورزش سه (عمومی)', 'url' => 'https://www.varzesh3.ir/rss/all', 'topic' => 'اخبار ورزشی' ),

		// --- سایما (سپیدار) ---
		array( 'name' => 'تلویزیون (سایما)', 'url' => 'https://iribonline.ir/Modules/News/Rss.aspx?AgencyID=120&portalid=1', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'شبکه خبر (سایما)', 'url' => 'https://iribonline.ir/Modules/News/Rss.aspx?AgencyID=133&portalid=1', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'شبکه ورزش (سایما)', 'url' => 'https://iribonline.ir/Modules/News/Rss.aspx?AgencyID=139&portalid=1', 'topic' => 'اخبار ورزشی' ),
		array( 'name' => 'سیاسی (سایما)', 'url' => 'https://iribonline.ir/Modules/News/Rss.aspx?AgencyID=373&portalid=1', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'برگزیده‌ها (سایما)', 'url' => 'https://iribonline.ir/Modules/News/Rss.aspx?AgencyID=381&portalid=1', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'فناوری (سایما)', 'url' => 'https://iribonline.ir/Modules/News/Rss.aspx?AgencyID=384&portalid=1', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'رادیو (سایما)', 'url' => 'https://iribonline.ir/Modules/News/Rss.aspx?AgencyID=119&portalid=1', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'استان‌ها (سایما)', 'url' => 'https://iribonline.ir/Modules/News/Rss.aspx?AgencyID=121&portalid=1', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'بین‌الملل (سایما)', 'url' => 'https://iribonline.ir/Modules/News/Rss.aspx?AgencyID=122&portalid=1', 'topic' => 'اخبار بین‌الملل' ),
		array( 'name' => 'سایر رسانه‌ها (سایما)', 'url' => 'https://iribonline.ir/Modules/News/Rss.aspx?AgencyID=123&portalid=1', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'یادداشت (سایما)', 'url' => 'https://iribonline.ir/Modules/News/Rss.aspx?AgencyID=129&portalid=1', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'ویژه‌ها (سایما)', 'url' => 'https://iribonline.ir/Modules/News/Rss.aspx?AgencyID=234&portalid=1', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'انتخابات (سایما)', 'url' => 'https://iribonline.ir/Modules/News/Rss.aspx?AgencyID=237&portalid=1', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'گزارش‌های پیشرفت (سایما)', 'url' => 'https://iribonline.ir/Modules/News/Rss.aspx?AgencyID=370&portalid=1', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'فضای مجازی (سایما)', 'url' => 'https://iribonline.ir/Modules/News/Rss.aspx?AgencyID=374&portalid=1', 'topic' => 'اخبار دانش و فناوری' ),

		// --- پارس نیوز ---
		array( 'name' => 'پارس نیوز (عمومی)', 'url' => 'https://www.parsnews.com/feeds/', 'topic' => 'اخبار سیاسی' ),

		// --- منابع تکمیلی (تأیید نشده از محیط توسعه؛ در صورت نیاز از پنل غیرفعال کنید) ---
		array( 'name' => 'تسنیم (عمومی)', 'url' => 'https://www.tasnimnews.com/fa/rss/allnews', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'خبر ۸ (عمومی)', 'url' => 'https://www.khabar8.com/feed', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'خبر ۳ (عمومی)', 'url' => 'https://www.khabar3.com/feed', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'زمین (عمومی)', 'url' => 'https://www.zamin.ir/feed', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'انتخاب (عمومی)', 'url' => 'https://www.entkhabar.com/feed', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'بیان ۲۴ (عمومی)', 'url' => 'https://www.bayan24.com/feed', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'دنیای نفت (عمومی)', 'url' => 'https://www.donyaneft.com/feed', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'فرهنگیان (عمومی)', 'url' => 'https://www.farhangian.ir/feed', 'topic' => 'اخبار فرهنگی و هنری' ),
		array( 'name' => 'پرس ۲۴۷ (عمومی)', 'url' => 'https://www.press247.ir/feed', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'ملت ۱۱۳۰ (عمومی)', 'url' => 'https://1130.mellat.co.ir/feed', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'تندار (عمومی)', 'url' => 'https://www.tondarnews.com/fa/rss/allnews', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'کیهان (عمومی)', 'url' => 'https://keyhan.ir/feed', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'اطلاعات (عمومی)', 'url' => 'https://www.etelaatnews.com/feed', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'همشهری (عمومی)', 'url' => 'https://www.hamshahri.ir/feed', 'topic' => 'اخبار اجتماعی' ),
		array( 'name' => 'تبناک (عمومی)', 'url' => 'https://www.tabnak.com/feed', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'ایبنا (عمومی)', 'url' => 'https://www.ibna.ir/fa/rss/allnews', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'برگ (عمومی)', 'url' => 'https://bargnews.com/fa/rss/allnews', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'سازندگی (عمومی)', 'url' => 'https://www.sazandegi.ir/feed', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'جمهوری (عمومی)', 'url' => 'https://www.jumhouri.ir/feed', 'topic' => 'اخبار سیاسی' ),
		array( 'name' => 'اسپرتنا (عمومی)', 'url' => 'https://www.sportna.ir/feed', 'topic' => 'اخبار ورزشی' ),
		array( 'name' => 'بورس ۲۴ (عمومی)', 'url' => 'https://bourse24.ir/feed', 'topic' => 'اخبار اقتصادی' ),
		array( 'name' => 'فوتبال آنلاین (عمومی)', 'url' => 'https://www.footbonline.com/feed', 'topic' => 'فوتبال' ),
		array( 'name' => 'پارسی‌دروید (عمومی)', 'url' => 'https://www.parsidroid.com/feed', 'topic' => 'اخبار دانش و فناوری' ),
		array( 'name' => 'ایران‌وایر (عمومی)', 'url' => 'https://iranwire.com/fa/feed', 'topic' => 'اخبار بین‌الملل' ),
		array( 'name' => 'وطن اقتصاد (عمومی)', 'url' => 'https://www.vatan-e-ektesad.com/feed', 'topic' => 'اخبار اقتصادی' ),
	);

	return apply_filters( 'khv_default_feeds', $feeds );
}

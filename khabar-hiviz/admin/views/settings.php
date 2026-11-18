<?php
/**
 * صفحه تنظیمات خبریاب.
 *
 * @package KhabarHiviz
 *
 * @var array  $settings   تنظیمات فعلی.
 * @var array  $models     مدل‌های گفتگویی API.
 * @var array  $post_types نوع‌های پست عمومی.
 * @var array  $cat_terms  فهرست دسته‌ها (برای selectها).
 * @var array  $logs       لاگ معکوس (جدیدترین اول).
 * @var string $notice     اعلان موقت.
 * @var int    $saved_flag نشان ذخیره‌شدن.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap khv-wrap">
	<h1>خبریاب هوشمند</h1>

	<?php if ( $saved_flag ) : ?>
		<div class="notice notice-success is-dismissible"><p>تنظیمات ذخیره شد و زمان‌بندی به‌روزرسانی شد.</p></div>
	<?php endif; ?>

	<?php if ( $notice ) : ?>
		<div class="notice notice-info is-dismissible"><p><?php echo esc_html( $notice ); ?></p></div>
	<?php endif; ?>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="khv_save_settings">
		<?php wp_nonce_field( 'khv_save', 'khv_nonce' ); ?>

		<!-- ================= هوش مصنوعی ================= -->
		<div class="khv-box">
			<h2>۱) تنظیمات هوش مصنوعی</h2>
			<div class="khv-row">
				<div class="khv-field">
					<label for="khv_api_url">آدرس API</label>
					<input type="url" id="khv_api_url" dir="ltr" name="api_url" value="<?php echo esc_attr( $settings['api_url'] ); ?>">
					<div class="desc">آدرس پایه API سازگار با OpenAI (بدون نیاز به افزودن /chat/completions)</div>
				</div>
				<div class="khv-field">
					<label for="khv_api_key">کلید API</label>
					<input type="password" id="khv_api_key" dir="ltr" name="api_key" value="" autocomplete="off" placeholder="<?php echo esc_attr( $settings['api_key_set'] ? 'یک کلید ذخیره شده است؛ برای تغییر، کلید جدید را وارد کنید' : 'sk-...' ); ?>" style="direction:ltr;text-align:left;">
					<div class="desc">
						<?php if ( ! empty( $settings['api_key_set'] ) ) : ?>
							<span class="khv-key-set">✅ کلید فعلی: <bdi dir="ltr"><?php echo esc_html( $settings['api_key_mask'] ); ?></bdi> — ذخیره‌شده به صورت رمزنگاری‌شده (AES-256-GCM) در دیتابیس.</span>
						<?php else : ?>
							کلید Secret (sk-...). این کلید در دیتابیس به‌صورت رمزنگاری‌شده ذخیره می‌شود و هرگز در فرم نمایش داده نمی‌شود.
						<?php endif; ?>
						<div>برای حفظ کلید فعلی، فیلد را خالی بگذارید؛ برای جایگزینی، کلید جدید را وارد کنید.</div>
					</div>
				</div>
				<div class="khv-field">
					<label for="khv_ai_model">مدل زبانی</label>
					<?php if ( ! empty( $models ) ) : ?>
						<select id="khv_ai_model" name="ai_model">
							<?php foreach ( $models as $model ) : ?>
								<option value="<?php echo esc_attr( $model ); ?>" <?php selected( $settings['ai_model'], $model ); ?>><?php echo esc_html( $model ); ?></option>
							<?php endforeach; ?>
						</select>
						<div class="desc">فهرست مدل‌ها از API خوانده شد؛ فقط مدل‌های گفتگویی نمایش داده شده‌اند.</div>
					<?php else : ?>
						<input type="text" id="khv_ai_model" dir="ltr" name="ai_model" value="<?php echo esc_attr( $settings['ai_model'] ); ?>">
						<div class="desc">دریافت فهرست مدل‌ها ناموفق بود؛ مدل را دستی وارد کنید.</div>
					<?php endif; ?>
				</div>
				<div class="khv-field khv-field-test">
					<label>&nbsp;</label>
					<button type="button" class="button" id="khv-test-ai">تست اتصال</button>
					<div class="khv-test-result" id="khv-test-result"></div>
				</div>
			</div>
		</div>

		<!-- ================= فیدها ================= -->
		<div class="khv-box">
			<h2>۲) فیدهای RSS و دسته مقصد</h2>
			<p class="description">هر فید به یک دسته‌بندی سایت متصل می‌شود. در هر چک، تا تعداد مشخص‌شده از <strong>جدیدترین</strong> خبرهای پردازش‌نشده آن فید به <strong>صف انتظار</strong> اضافه می‌شود و سپس در تیک‌های پردازش (هر ۱ دقیقه) به‌ترتیب زمان انتشار و با هوش مصنوعی به پیش‌نویس تبدیل می‌شوند تا هاست با timeout مواجه نشود. خبرهای تکراری (بر اساس آدرس) خودکار رد می‌شوند و خطاهای موقت با فاصلهٔ افزایشی دوباره امتحان می‌شوند.</p>
			<table class="khv-table widefat striped" id="khv-feeds">
				<thead>
					<tr>
						<th style="min-width:150px;">نام فید</th>
						<th style="min-width:260px;">آدرس فید</th>
						<th style="min-width:180px;">دسته مقصد</th>
						<th style="width:60px;">فعال</th>
						<th style="width:90px;">حداکثر خبر</th>
						<th style="min-width:150px;">تست RSS</th>
						<th style="width:50px;"></th>
					</tr>
				</thead>
				<tbody id="khv-feed-tbody">
					<?php foreach ( $settings['feeds'] as $i => $feed ) : ?>
						<tr>
							<td>
								<input type="text" name="khv_feeds[<?php echo (int) $i; ?>][name]" value="<?php echo esc_attr( $feed['name'] ); ?>" placeholder="مثلا: فوتبال">
							</td>
							<td class="khv-cell-url">
								<input type="url" dir="ltr" name="khv_feeds[<?php echo (int) $i; ?>][url]" value="<?php echo esc_attr( $feed['url'] ); ?>" placeholder="https://example.com/rss">
							</td>
							<td>
								<select name="khv_feeds[<?php echo (int) $i; ?>][category]" class="khv-cat-select">
									<option value="0">دسته پیش‌فرض</option>
									<?php foreach ( $cat_terms as $t ) : ?>
										<option value="<?php echo (int) $t->term_id; ?>" <?php selected( (int) $feed['category'], (int) $t->term_id ); ?>><?php echo esc_html( $t->name ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
							<td class="khv-cell-ck">
								<input type="checkbox" name="khv_feeds[<?php echo (int) $i; ?>][enabled]" value="1" <?php checked( ! empty( $feed['enabled'] ) ); ?>>
							</td>
							<td class="khv-cell-num">
								<input type="number" name="khv_feeds[<?php echo (int) $i; ?>][max_items]" value="<?php echo (int) $feed['max_items']; ?>" min="1" max="50">
							</td>
							<td class="khv-cell-test">
								<button type="button" class="button khv-test-feed" data-url="<?php echo esc_attr( $feed['url'] ); ?>">تست</button>
								<span class="khv-feed-test-msg" style="display:none;"></span>
							</td>
							<td class="khv-cell-del"><a href="#" class="khv-del" title="حذف این فید">✕</a></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p>
				<button type="button" class="button" id="khv-add-feed">+ افزودن فید جدید</button>
				<button type="button" class="button button-primary" id="khv-ensure-cats">ساخت خودکار دسته‌های کم‌تعداد</button>
				<span id="khv-cats-msg" class="khv-run-msg" style="display:none;"></span>
			</p>
			<p class="description">برای فیدهایی که دسته مقصد انتخاب‌نشده دارند، دسته‌ای به‌همان نام فید (بدون نام منبع در پرانتز) ساخته می‌شود یا به دسته موجودی با همان نام متصل می‌گردد. این کار هنگام فعال‌سازی افزونه خودکار انجام شد و هر بار می‌توانید تکرارش کنید؛ فیدهایی که قبلاً دسته انتخاب کرده‌اید دست‌نخورده می‌مانند.</p>
			<p class="description">نمونه فید فوتبال خبرورزشی: <span dir="ltr">https://www.khabarvarzeshi.com/rss/tp/1</span></p>
		</div>

		<!-- ================= درج پیش‌نویس ================= -->
		<div class="khv-box">
			<h2>۳) نحوه درج پیش‌نویس</h2>
			<div class="khv-row">
				<div class="khv-field">
					<label for="khv_post_type">نوع پست</label>
					<select id="khv_post_type" name="post_type">
						<?php foreach ( $post_types as $pt ) : ?>
							<option value="<?php echo esc_attr( $pt ); ?>" <?php selected( $settings['post_type'], $pt ); ?>><?php echo esc_html( $pt ); ?></option>
						<?php endforeach; ?>
					</select>
					<div class="desc">پیش‌نویس با این نوع پست ساخته می‌شود.</div>
				</div>
				<div class="khv-field">
					<label for="khv_post_status">وضعیت پست</label>
					<select id="khv_post_status" name="post_status">
						<option value="draft" <?php selected( $settings['post_status'], 'draft' ); ?>>پیش‌نویس</option>
						<option value="publish" <?php selected( $settings['post_status'], 'publish' ); ?>>منتشرشده (مستقیم)</option>
						<option value="pending" <?php selected( $settings['post_status'], 'pending' ); ?>>در انتظار بازبینی</option>
					</select>
					<div class="desc">پیش‌فرض «پیش‌نویس» است تا قبل از انتشار بازبینی شود.</div>
				</div>
				<div class="khv-field">
					<label for="khv_interval">بازه چک فیدها (اسکن و پرکردن صف)</label>
					<select id="khv_interval" name="interval">
						<option value="khv_5min" <?php selected( $settings['interval'], 'khv_5min' ); ?>>هر ۵ دقیقه</option>
						<option value="khv_15min" <?php selected( $settings['interval'], 'khv_15min' ); ?>>هر ۱۵ دقیقه</option>
						<option value="khv_30min" <?php selected( $settings['interval'], 'khv_30min' ); ?>>هر ۳۰ دقیقه</option>
						<option value="hourly" <?php selected( $settings['interval'], 'hourly' ); ?>>هر ۱ ساعت</option>
						<option value="daily" <?php selected( $settings['interval'], 'daily' ); ?>>روزی</option>
						<option value="manual" <?php selected( $settings['interval'], 'manual' ); ?>>فقط دستی (دکمه اجرا)</option>
					</select>
					<div class="desc">پردازش صف جدا از این بازه و <strong>هر ۱ دقیقه</strong> اجرا می‌شود (تا سقف «حداکثر خبر در هر تیک»). Cron وردپرس فقط وقتی اجرا می‌شود که ترافیک بازدید روی سایت باشد؛ اگر سایت کم‌ترافیک است از cron سیستمی یا افزونه‌هایی مثل «Always Online» استفاده کنید.</div>
				</div>
				<div class="khv-field">
					<label for="khv_max_items_per_run">حداکثر خبر در هر تیک پردازش صف</label>
					<input type="number" id="khv_max_items_per_run" name="max_items_per_run" value="<?php echo (int) $settings['max_items_per_run']; ?>" min="1" max="100">
					<div class="desc">هر ۱ دقیقه، این تعداد خبر از صف با هوش مصنوعی پردازش می‌شود. مقدار پایین (۲ تا ۵) برای هاست‌های اشتراکی امن‌تر است.</div>
				</div>
				<div class="khv-field">
					<label for="khv_scan_batches">تعداد گروه‌های اسکن فیدها</label>
					<input type="number" id="khv_scan_batches" name="scan_batches" value="<?php echo (int) $settings['scan_batches']; ?>" min="1" max="20">
					<div class="desc">هر چک، فقط یک گروه از فیدها به‌صورت چرخشی اسکن می‌شود تا با تعداد زیاد فید، زمان اجرا منطقی بماند. مثلاً با ۳ گروه و چک هر ۵ دقیقه، کل فیدها هر ۱۵ دقیقه یک‌بار کامل چک می‌شوند. مقدار ۱ یعنی همه فیدها در هر چک.</div>
				</div>
			</div>
		</div>

		<!-- ================= متن و تصویر ================= -->
		<div class="khv-box">
			<h2>۴) متن، تصویر و کلیدواژه</h2>
			<div class="khv-row">
				<div class="khv-field">
					<label for="khv_image_mode">نحوه تصویر خبر</label>
					<select id="khv_image_mode" name="image_mode">
						<option value="remote" <?php selected( $settings['image_mode'], 'remote' ); ?>>نمایش مستقیم از CDN مبدأ (بدون دانلود)</option>
						<option value="download" <?php selected( $settings['image_mode'], 'download' ); ?>>دانلود و ثبت در کتابخانه رسانه</option>
					</select>
					<div class="desc">در حالت «مستقیم»، تصویر به‌صورت attachment با آدرس خارجی ثبت می‌شود و از CDN سایت مبدأ لود می‌شود.</div>
				</div>
				<div class="khv-field">
					<label for="khv_keywords_mode">مقصد کلیدواژه‌ها</label>
					<select id="khv_keywords_mode" name="keywords_mode">
						<option value="tags" <?php selected( $settings['keywords_mode'], 'tags' ); ?>>برچسب‌های وردپرس</option>
						<option value="yoast" <?php selected( $settings['keywords_mode'], 'yoast' ); ?>>کلمه کلیدی هدف Yoast (اولین کلیدواژه)</option>
						<option value="rankmath" <?php selected( $settings['keywords_mode'], 'rankmath' ); ?>>کلمه کلیدی هدف RankMath (اولین کلیدواژه)</option>
						<option value="meta" <?php selected( $settings['keywords_mode'], 'meta' ); ?>>فیلد اختصاصی _khv_keywords</option>
					</select>
				</div>
				<div class="khv-field">
					<label for="khv_attribution">ذکر منبع</label>
					<select id="khv_attribution" name="attribution">
						<option value="none" <?php selected( $settings['attribution'], 'none' ); ?>>بدون ذکر منبع (کاملاً پاک‌شده)</option>
						<option value="line" <?php selected( $settings['attribution'], 'line' ); ?>>یک خط «منبع: …» با لینک به خبر اصلی</option>
					</select>
					<div class="desc">ارجاعات درون متن (مثل «به گزارش ...») همیشه با هوش مصنوعی حذف می‌شوند؛ این گزینه فقط خط منبع پایانی را کنترل می‌کند.</div>
				</div>
			</div>
			<div class="khv-row">
				<div class="khv-field">
					<label for="khv_skip_cats">رد دسته‌های این فید‌ها</label>
					<input type="text" id="khv_skip_cats" name="skip_cats" value="<?php echo esc_attr( $settings['skip_cats'] ); ?>" placeholder="ویدیو">
					<div class="desc">اگر در دسته (category) آیتم فید این متن‌ها باشد، رد می‌شود. جداشونده با ویرگول.</div>
				</div>
				<div class="khv-field">
					<label for="khv_max_text">حداکثر کاراکتر متن ارسالی به هوش مصنوعی</label>
					<input type="number" id="khv_max_text" name="max_text_chars" value="<?php echo (int) $settings['max_text_chars']; ?>" min="500" max="20000" step="100">
					<div class="desc">متون طولانی‌تر تا این حد کوتاه می‌شوند.</div>
				</div>
			</div>
		</div>

		<p>
			<button type="submit" class="button button-primary button-hero">ذخیره تنظیمات</button>
		</p>
	</form>

	<!-- ================= اجرا و لاگ ================= -->
	<div class="khv-box">
		<h2>۵) اجرا، صف و لاگ</h2>
		<?php if ( KHV_Processor::is_paused() ) : ?>
			<div class="notice notice-warning inline"><p>⏸ پردازش فعلاً <strong>متوقف</strong> است؛ فیدها چک می‌شوند ولی خبری پردازش نمی‌شود.</p></div>
		<?php endif; ?>
		<p>
			<button type="button" class="button button-primary" id="khv-run-now">اجرای فوری</button>
			<span id="khv-run-msg" class="khv-run-msg" style="display:none;"></span>
			<?php if ( KHV_Cron::is_running() ) : ?>
				<span class="notice notice-warning inline"><p>همین حالا یک اجرا در حال انجام است.</p></span>
			<?php endif; ?>
		</p>
		<p class="description">اجرای فوری: اسکن همه فیدها + پردازش صف تا پایان زمان مجاز (۵ دقیقه). بقیهٔ صف در تیک‌های cron (هر ۱ دقیقه) پردازش می‌شود. بعد از اتمام، صفحه به‌روزرسانی می‌شود.</p>
		<p class="khv-queue-status">
			در صف انتظار: <strong><?php echo (int) KHV_Processor::queue_count(); ?></strong> خبر
			<?php
			$cutoff_ts = KHV_Processor::get_cutoff();
			if ( $cutoff_ts > 0 ) {
				echo '&nbsp;|&nbsp; نقطهٔ شروع: فقط خبرهای تازه‌تر از <strong>' . esc_html( date_i18n( 'Y/m/d H:i', $cutoff_ts ) ) . '</strong>';
			}
			?>
		</p>
		<p class="khv-control-row">
			<?php if ( KHV_Processor::is_paused() ) : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="khv-inline-form">
					<input type="hidden" name="action" value="khv_toggle_pause">
					<?php wp_nonce_field( 'khv_toggle_pause' ); ?>
					<button type="submit" class="button">▶ ادامه پردازش</button>
				</form>
			<?php else : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="khv-inline-form" onsubmit="return confirm('پردازش متوقف می‌شود؛ صف و تنظیمات حفظ می‌شوند. مطمئن هستید؟');">
					<input type="hidden" name="action" value="khv_toggle_pause">
					<?php wp_nonce_field( 'khv_toggle_pause' ); ?>
					<button type="submit" class="button">⏸ توقف پردازش</button>
				</form>
			<?php endif; ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="khv-inline-form" onsubmit="return confirm('مهم: فقط خبرهایی که بعد از این لحظه منتشر شوند پردازش می‌شوند و صف کنونی خالی می‌شود. مطمئن هستید؟');">
				<input type="hidden" name="action" value="khv_start_from_now">
				<?php wp_nonce_field( 'khv_start_from_now' ); ?>
				<button type="submit" class="button">✂ شروع از این لحظه (نادیده‌گرفتن خبرهای قدیمی)</button>
			</form>
		</p>
		<p class="description">«شروع از این لحظه» مفید است وقتی می‌خواهید فقط از الان خبر جمع کنید و از بابت خبرهای کهنهٔ فیدها نگران نباشید.</p>

		<h3>لاگ (آخرین ۲۰۰ رویداد)</h3>
		<div class="khv-log">
			<?php if ( empty( $logs ) ) : ?>
				<div class="khv-log-row">هنوز رویدادی ثبت نشده است.</div>
			<?php else : ?>
				<?php foreach ( $logs as $log ) : ?>
					<div class="khv-log-row khv-lvl-<?php echo esc_attr( $log['lvl'] ); ?>">
						<span><?php echo esc_html( $log['msg'] ); ?></span>
						<span class="t"><?php echo esc_html( date_i18n( 'Y/m/d H:i', $log['t'] ) ); ?></span>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		<p>
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=khv_clear_log' ), 'khv_clear_log' ) ); ?>" class="button" onclick="return confirm('لاگ پاک شود؟');">پاک‌کردن لاگ</a>
		</p>
	</div>
</div>

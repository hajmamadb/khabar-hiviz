/* global jQuery, khvData */
/**
 * خبریاب هوشمند - اسکریپت صفحه مدیریت.
 */
(function ($) {
	'use strict';

	$(function () {

		var $tbody = $('#khv-feed-tbody');
		var nextIdx = $tbody.find('tr').length;

		function catOptions() {
			var html = '<option value="0">' + khvData.catDefault + '</option>';
			$.each(khvData.cats, function (id, name) {
				var safe = String(name).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
				html += '<option value="' + id + '">' + safe + '</option>';
			});
			return html;
		}

		// افزودن ردیف فید جدید
		$('#khv-add-feed').on('click', function () {
			var i = nextIdx++;
			var row =
				'<tr>' +
				'<td><input type="text" name="khv_feeds[' + i + '][name]" value="" placeholder="مثلا: فوتبال"></td>' +
				'<td class="khv-cell-url"><input type="url" dir="ltr" name="khv_feeds[' + i + '][url]" value="" placeholder="https://example.com/rss"></td>' +
				'<td><select name="khv_feeds[' + i + '][category]">' + catOptions() + '</select></td>' +
				'<td class="khv-cell-ck"><input type="checkbox" name="khv_feeds[' + i + '][enabled]" value="1" checked></td>' +
				'<td class="khv-cell-num"><input type="number" name="khv_feeds[' + i + '][max_items]" value="5" min="1" max="50"></td>' +
				'<td class="khv-cell-test"><button type="button" class="button khv-test-feed" data-url="">تست</button><span class="khv-feed-test-msg" style="display:none;"></span></td>' +
				'<td class="khv-cell-del"><a href="#" class="khv-del" title="حذف این فید">✕</a></td>' +
				'</tr>';
			$tbody.append(row);
		});

		// حذف فید
		$tbody.on('click', '.khv-del', function (e) {
			e.preventDefault();
			$(this).closest('tr').remove();
		});

		// تست RSS یک فید
		$tbody.on('click', '.khv-test-feed', function () {
			var $btn = $(this);
			var $msg = $btn.siblings('.khv-feed-test-msg').text('').removeClass('ok err').hide();
			var url = $btn.data('url') || $btn.closest('tr').find('input[type="url"]').val() || '';

			if (!url) {
				$msg.text('اول آدرس فید را وارد کنید.').addClass('err').show();
				return;
			}

			$btn.prop('disabled', true).text('در حال تست…');
			$.ajax({
				url: khvData.ajaxUrl,
				method: 'POST',
				data: { action: 'khv_test_feed', nonce: khvData.nonce, url: url },
				timeout: 60000
			})
				.done(function (r) {
					$msg.text((r && r.data) || String(r)).addClass(r && r.success ? 'ok' : 'err').show();
				})
				.fail(function (xhr) {
					var msg = 'خطا در ارتباط با سرور وردپرس.';
					try {
						var parsed = JSON.parse(xhr.responseText);
						if (parsed && parsed.data) { msg = parsed.data; }
					} catch (e) { /* ignore */ }
					$msg.text(msg).addClass('err').show();
				})
				.always(function () {
					$btn.prop('disabled', false).text('تست');
				});
		});

		// ساخت خودکار دسته‌های کم‌تعداد
		$('#khv-ensure-cats').on('click', function () {
			var $btn = $(this).prop('disabled', true).text('در حال ساخت دسته‌ها…');
			var $msg = $('#khv-cats-msg').text('').hide();

			$.post(khvData.ajaxUrl, { action: 'khv_ensure_cats', nonce: khvData.nonce })
				.done(function (r) {
					$msg.text((r && r.data) || 'انجام شد.').show();
					if (r && r.success) {
						// برای به‌روزرسانی فهرست دسته‌ها در دراپ‌داون‌ها، صفحه ریلود می‌شود.
						setTimeout(function () { window.location.reload(); }, 1500);
					}
				})
				.fail(function (xhr) {
					var msg = 'خطا در ساخت دسته‌ها.';
					try {
						var parsed = JSON.parse(xhr.responseText);
						if (parsed && parsed.data) { msg = parsed.data; }
					} catch (e) { /* ignore */ }
					$msg.text(msg).show();
				})
				.always(function () {
					$btn.prop('disabled', false).text('ساخت خودکار دسته‌های کم‌تعداد');
				});
		});

		// تست اتصال به هوش مصنوعی
		$('#khv-test-ai').on('click', function () {
			var $btn = $(this).prop('disabled', true).text('در حال تست…');
			var $res = $('#khv-test-result').text('').removeClass('ok err');

			$.post(khvData.ajaxUrl, { action: 'khv_test_ai', nonce: khvData.nonce })
				.done(function (r) {
					$res.text((r && r.data) || String(r)).addClass(r && r.success ? 'ok' : 'err');
				})
				.fail(function () {
					$res.text('خطا در ارتباط با سرور وردپرس').addClass('err');
				})
				.always(function () {
					$btn.prop('disabled', false).text('تست اتصال');
				});
		});

		// اجرای فوری
		$('#khv-run-now').on('click', function () {
			var $btn = $(this).prop('disabled', true).text('در حال اجرا…');
			var $msg = $('#khv-run-msg').text('در حال بررسی فیدها و ساخت پیش‌نویس‌ها… (ممکن است چند دقیقه طول بکشد)').show();

			$.ajax({
				url: khvData.ajaxUrl,
				method: 'POST',
				data: { action: 'khv_run_now', nonce: khvData.nonce },
				timeout: 0
			})
				.done(function (r) {
					$msg.text((r && r.data) || 'اجرا کامل شد.').show();
					setTimeout(function () { window.location.reload(); }, 1500);
				})
				.fail(function (xhr) {
					var msg = 'خطا در اجرای درخواست؛ لاگ را بررسی کنید.';
					try {
						var parsed = JSON.parse(xhr.responseText);
						if (parsed && parsed.data) { msg = parsed.data; }
					} catch (e) { /* ignore */ }
					$msg.text(msg).show();
				})
				.always(function () {
					$btn.prop('disabled', false).text('اجرای فوری');
				});
		});

	});
})(jQuery);

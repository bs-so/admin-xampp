(function ($) {
	var initLayout = function () {
		var hash = window.location.hash.replace('#', '');
		var currentTab = $('ul.navigationTabs a')
			.bind('click', showTab)
			.filter('a[rel=' + hash + ']');
		if (currentTab.size() == 0) {
			currentTab = $('ul.navigationTabs a:first');
		}
		showTab.apply(currentTab.get(0));

		$('#colorpickerHolder1').ColorPicker({
			flat: true,
			color: '#' + $('#color1').val(),
			onChange: function (hsb, hex, rgb) {
				$('#color1').val(hex);
			}
		});
		$('#colorpickerHolder2').ColorPicker({
			flat: true,
			color: '#' + $('#color2').val(),
			onChange: function (hsb, hex, rgb) {
				$('#color2').val(hex);
			}
		});
		$('#colorpickerHolder3').ColorPicker({
			flat: true,
			color: '#' + $('#color3').val(),
			onChange: function (hsb, hex, rgb) {
				$('#color3').val(hex);
			}
		});

		$('#colorpickerField1, #colorpickerField2, #colorpickerField3').ColorPicker({
			onSubmit: function (hsb, hex, rgb, el) {
				$(el).val(hex);
				$(el).ColorPickerHide();
			},
			onBeforeShow: function () {
				$(this).ColorPickerSetColor(this.value);
			}
		})
			.bind('keyup', function () {
				$(this).ColorPickerSetColor(this.value);
			});
		$('#colorSelector').ColorPicker({
			color: '#0000ff',
			onShow: function (colpkr) {
				$(colpkr).fadeIn(500);
				return false;
			},
			onHide: function (colpkr) {
				$(colpkr).fadeOut(500);
				return false;
			},
			onChange: function (hsb, hex, rgb) {
				$('#colorSelector div').css('backgroundColor', '#' + hex);
			}
		});
	};

	var showTab = function (e) {
		var tabIndex = $('ul.navigationTabs a')
			.removeClass('active')
			.index(this);
		$(this)
			.addClass('active')
			.blur();
		$('div.tab')
			.hide()
			.eq(tabIndex)
			.show();
	};

	EYE.register(initLayout, 'init');
})(jQuery)
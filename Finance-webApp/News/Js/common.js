$(function () {
	$('html').css('font-size', document.documentElement.clientHeight / document.documentElement.clientWidth < 1.5 ? document.documentElement.clientHeight / 603 * 312.5 + '%' : document.documentElement.clientWidth / 375 * 312.5 + '%');
});

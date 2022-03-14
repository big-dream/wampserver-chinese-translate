<?php
$pageContents .= <<< EOF
<script>
/*Copy modal dialog contents to clipboard */
var btncopy = document.querySelector('.js-copy');
if(btncopy) {
  btncopy.addEventListener('click', docopy);
}
var btncopy = document.querySelector('.js-copya');
if(btncopy) {
  btncopy.addEventListener('click', docopy);
}
var btncopy = document.querySelector('.js-copyb');
if(btncopy) {
  btncopy.addEventListener('click', docopy);
}
var btncopy = document.querySelector('.js-copyc');
if(btncopy) {
  btncopy.addEventListener('click', docopy);
}
function docopy() {
	var range = document.createRange();
	var target = this.dataset.target;
	var fromElement = document.querySelector(target);
	var selection = window.getSelection();

	range.selectNode(fromElement);
	selection.removeAllRanges();
	selection.addRange(range);

	try {
		var result = document.execCommand('copy');
		if (result) {
		  window.alert('Copied');
		}
	}
	catch(err) {
		window.alert(err);
	}
	selection = window.getSelection();
	if (typeof selection.removeRange === 'function') {
	  selection.removeRange(range);
	} else if (typeof selection.removeAllRanges === 'function') {
	  selection.removeAllRanges();
	}
}
</script>
EOF;
?>

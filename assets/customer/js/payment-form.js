/**
 * Payment gateway - Jeeb
 *
 * Retrieve payments using jeeb
 *
 * @package MasihFathi
 * @subpackage Payment Gateway Jeeb.io
 * @author Masih Fathi <masihfathi@gmail.com>
 * @link https://www.avangemail.com/
 */
jQuery(document).ready(function($){
	
	var ajaxData = {};
	if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
			var csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
			var csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
			ajaxData[csrfTokenName] = csrfTokenValue;
	}
    
    $('#jeeb-hidden-form').on('submit', function(){
        var $this = $(this);
        if ($this.data('submit')) {
            return true;
        }
        if ($this.data('ajaxRunning')) {
            return false;
        }
        $this.data('ajaxRunning', true);
        $.post($this.data('order'), $this.serialize(), function(json){
            $this.data('ajaxRunning', false);
            if (json.status == 'error') {
                notify.remove().addError(json.message).show();
            } else {
                $this.data('submit', true).submit();
            }
        }, 'json');
        return false;
    });
});
<?php 
/**
 * Nexcloud - RCDevs OpenOTP Two-factor Authentication
 *
 * @package twofactor_rcdevsopenotp
 * @author Julien RICHARD
 * @copyright 2017 RCDEVS info@rcdevs.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * Displays <a href="http://opensource.org/licenses/AGPL-3.0">GNU AFFERO GENERAL PUBLIC LICENSE</a>
 * @license http://opensource.org/licenses/AGPL-3.0 GNU AFFERO GENERAL PUBLIC LICENSE
 *
 */
$rcdevsopenotp_message = "";
if(is_array($_['challenge_params'])) extract($_['challenge_params']);
?>
<style>
html #body-login .warning{ margin:0; }
</style>

<?php if ($_['error_msg']): ?>
    <fieldset style="margin-top:6px;" class="warning">
        <ul>
            <?php foreach ($_['error_msg'] as $message): ?>
                <li><?php p($message); ?></li>
            <?php endforeach; ?>
        </ul>
        <!--
        Debug:
        <?php print_r($_); ?>
        -->
    </fieldset>
<?php endif; ?>


<form method="POST" id="OpenOTPLoginForm" name="LoginForm">
	
	<?php if($_['status'] && $_['status'] == "pushSuccess") { ?>
		<p>You have been connected<br/> You will be redirected in 2 seconds</p>
		<input type="hidden" name="challenge" value="passme" />
		<input type="hidden" name="rcdevsopenotp_nonce" value="<?php p($_['challenge_params']['rcdevsopenotp_nonce']);?>" />
	<?php }else{ ?>	
	
		<p>Hello <?php p($_['userID']); 
		if($rcdevsopenotp_message): 
			echo ",<br/><b>"; 
			p($rcdevsopenotp_message); 
			echo "</b>"; 
		endif; ?>
		</p>
		<?php if(!$_['error_msg']){ ?>
			<p id="timout_cell" style="padding:10px 0; font-style:italic;">Timeout: <span id="timeout"><?php p($rcdevsopenotp_timeout); ?> seconds</p>
			<?php foreach( $_['challenge_params'] as $param => $val){ ?>
				<input type="hidden" name="<?php p($param); ?>" value="<?php p($val); ?>">
			<?php } ?>
			
			<?php if( $rcdevsopenotp_u2fChallenge ){ ?>
				<input type="hidden" name="openotp_u2f" value="">
				<input type="hidden" name="challenge" value="" />
				<div id="u2f_display" class="display">
				<?php if( $rcdevsopenotp_otpChallenge ){ ?>
					<b>U2F response</b> &nbsp; <blink id="u2f_activate">[Activate Device]</blink>
				<?php }else{ ?>
					<p style="text-align:center;padding-top:10px;">
						<img src="<?php p($rcdevsopenotp_appWebPath) ?>/img/u2f.png"><br/>
						<blink id="u2f_activate">[Activate Device]</blink>
					</p>
				<?php } ?>		
				</div>	
			<?php } ?>			
			
			<?php if( $rcdevsopenotp_otpChallenge || ( !$rcdevsopenotp_otpChallenge && !$rcdevsopenotp_u2fChallenge ) ){ ?>
			<div id="actions" class="display">
				<input type="password" id="openotp_password" name="challenge" placeholder="One Time Password" autocomplete="off" autocorrect="off" required autofocus/>
				<input type="submit" id="openotp_submit" class="login primary icon-confirm-white" title="" value="Login" />
			</div>
			<?php } ?>		
			<div id="retry"></div>
		<?php }else{ ?>
				<input type="button" id="openotp_retry" class="login primary icon-confirm-white" title="" value="Retry" />		
		<?php } ?>

	<?php } ?>
</form>

<script type="text/javascript" nonce="<?php p(\OC::$server->getContentSecurityPolicyNonceManager()->getNonce()) ?>">
	
	$(document).ready(function () {
	/* Compute Timeout */	
		
	/*Handle Push Challenge*/
	<?php if($_['status'] && $_['status'] == "pushSuccess") { ?>
		$("#OpenOTPLoginForm").submit();
	<?php } ?>
	
	/*Get Retry Button location*/
	var logout_attr = '<?php print_unescaped($_['logout_attr']); ?>';	
	var regex = /href=\"(.*)\"/;
	var match = regex.exec(logout_attr);
	var retry_url = match[1];
	
	if ( $("#openotp_retry").length ) {
		$(this).on('click', function(){
		    window.location = retry_url;    
		});
	}	
	
	$(document).arrive("#openotp_retry", function() {
		$("#openotp_retry").on( "click", function() {
		    //window.location = retry_url;    
		    window.location = "";    
		});
	});
	
	<?php if($rcdevsopenotp_u2fChallenge) { ?>
	/*U2F*/
		if (/chrome|chromium|firefox|opera/.test(navigator.userAgent.toLowerCase())) { 
		    var u2f_request = <?php echo $rcdevsopenotp_u2fChallenge; ?>;
		    var u2f_regkeys = [];
		    for (var i=0, len=u2f_request.keyHandles.length; i<len; i++) {
		        u2f_regkeys.push({version:u2f_request.version,keyHandle:u2f_request.keyHandles[i]});
		    }
		    u2f.sign(u2f_request.appId, u2f_request.challenge, u2f_regkeys, function(response) {
				if(response.errorCode){
					$('#retry').html('<input type="button" id="openotp_retry" class="login primary icon-confirm-white" title="" value="Retry" />');
					//$('#OpenOTPLoginForm').append('<input type="button" id="openotp_retry" class="login primary icon-confirm-white" title="" value="Retry" />');
					$('#u2f_display').hide();
					console.log("OpenOTP Fido U2F signature Log #Code:" + response.errorCode);
				}else{ document.getElementsByName('openotp_u2f')[0].value = JSON.stringify(response); 
					document.getElementsByName('challenge')[0].value = "dummy"; 
					$('#OpenOTPLoginForm').submit();					
				}
		    }, <?php echo $rcdevsopenotp_timeout+1; ?> ); 
		} else { 
			$('#u2f_activate').html('[Not Supported]'); 
			$('#u2f_activate').css('color','red'); 
		}
	<?php } ?>
	

	<?php if(!$_['error_msg'] && ($_['status'] && $_['status'] != "pushSuccess")):?>
		
	var c = <?php p($rcdevsopenotp_timeout); ?>;
	var base = <?php p($rcdevsopenotp_timeout); ?>;
	function count()
	{
		plural = c <= 1 ? "" : "s";
		$("#timeout").html(c + " second" + plural);
		var div_width = 300;
		var new_width =  Math.round(c*div_width/base);
		$('#div_orange').css('width',new_width+'px');

		if(c == 0 || c < 0) {
			c = 0;
			clearInterval(timer);
			$("#timout_cell").html("<b style='color:red;'>Login timedout!</b>");
			$(".display").html("");
			$('#retry').html('<input type="button" id="openotp_retry" class="login primary icon-confirm-white" title="" value="Retry" />');
			//$('#OpenOTPLoginForm').append('<input type="button" id="openotp_retry" class="login primary icon-confirm-white" title="" value="Retry" />');
		}
		c--;
	}
	count();
	
	function getInternetExplorerVersion() {
	
		var rv = -1;
	
		if (navigator.appName == "Microsoft Internet Explorer") {
			var ua = navigator.userAgent;
			var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
			if (re.exec(ua) != null)
				rv = parseFloat(RegExp.$1);
		}
		return rv;
	}
	
	var ver = getInternetExplorerVersion();
	
	if (navigator.appName == "Microsoft Internet Explorer"){
		if (ver <= 10){
			toggleItem = function(){
				
			    var el = document.getElementsByTagName("blink")[0];
			    if (el.style.display === "block") {
			        el.style.display = "none";
			    } else {
			        el.style.display = "block";
			    }
			}
			var t = setInterval(function() {toggleItem; }, 1000);
		}
	}
	
	var timer = setInterval(function() {count();  }, 1000);
	
	<?php endif; ?>
});
</script>

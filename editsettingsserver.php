<?php
    
	require_once('./php/CRMDefaults.php');
	require_once('./php/UIHandler.php');
	//require_once('./php/DbHandler.php');
	require_once('./php/LanguageHandler.php');
	require('./php/Session.php');
	require_once('./php/goCRMAPISettings.php');

	// initialize structures
	$ui = \creamy\UIHandler::getInstance();
	$lh = \creamy\LanguageHandler::getInstance();
	$user = \creamy\CreamyUser::currentUser();

$id = NULL;
if (isset($_POST["id"])) {
	$id = $_POST["id"];
}else{
	header("location: settingsservers.php");
}

?>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?php $lh->translateText('portal_title'); ?> - <?php $lh->translateText("edit_server"); ?></title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

        <!-- Call for standardized css -->
        <?php print $ui->standardizedThemeCSS();?>
        <?php print $ui->creamyThemeCSS(); ?>
		
		<!-- SELECT2-->
   		<link rel="stylesheet" href="theme_dashboard/select2/dist/css/select2.css">
   		<link rel="stylesheet" href="theme_dashboard/select2-bootstrap-theme/dist/select2-bootstrap.css">
   		<!-- SELECT2-->
   		<script src="theme_dashboard/select2/dist/js/select2.js"></script>
    </head>
    <?php print $ui->creamyBody(); ?>
        <div class="wrapper">
        <!-- header logo: style can be found in header.less -->
		<?php print $ui->creamyHeader($user); ?>
            <!-- Left side column. contains the logo and sidebar -->
			<?php print $ui->getSidebar($user->getUserId(), $user->getUserName(), $user->getUserRole(), $user->getUserAvatar()); ?>

            <!-- Right side column. Contains the navbar and content of the page -->
            <aside class="right-side">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <h1 style="font-weight:normal;">
                        <?php $lh->translateText("settings"); ?>
                        <small><?php $lh->translateText("Carrier Edit"); ?></small>
                    </h1>
                    <ol class="breadcrumb">
                        <li><a href="./index.php"><i class="fa fa-edit"></i> <?php $lh->translateText("home"); ?></a></li>
                        <li> <?php $lh->translateText("settings"); ?></li>
                        <?php
							if(isset($_POST["id"])){
						?>	
							<li><a href="./settingsservers.php"><?php $lh->translateText("servers"); ?></a></li>
                        <?php
							}
                        ?>	                    
                        <li class="active"><?php $lh->translateText("modify"); ?></li>
                    </ol>
                </section>
                <!-- Main content -->
	            <section class="content">
					<div class="panel panel-default">
	                    <div class="panel-body">
						<!-- standard custom edition form -->

						<?php
						$errormessage = NULL;
						
						if(isset($id)) {
							$url = gourl."/goServers/goAPI.php"; #URL to GoAutoDial API. (required)
					        $postfields["goUser"] = goUser; #Username goes here. (required)
					        $postfields["goPass"] = goPass; #Password goes here. (required)
					        $postfields["goAction"] = "goGetServerInfo"; #action performed by the [[API:Functions]]. (required)
					        $postfields["responsetype"] = responsetype; #json. (required)
					        $postfields["server_id"] = $id; #Desired exten ID. (required)
							$postfields["log_user"] = $_SESSION['user'];
							$postfields["log_group"] = $_SESSION['usergroup'];
							$postfields["hostname"] = $_SERVER['REMOTE_ADDR'];

							$ch = curl_init();
							curl_setopt($ch, CURLOPT_URL, $url);
							curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
							curl_setopt($ch, CURLOPT_POST, 1);
							curl_setopt($ch, CURLOPT_TIMEOUT, 100);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
							curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
							$data = curl_exec($ch);
							curl_close($ch);
							$output = json_decode($data);
					        
					        //var_dump($output->data);
							
							if ($output->result=="success") {
								
							$user_groups = $ui->API_goGetUserGroupsList();
						?>

				<legend><?php $lh->translateText("modify_server_id"); ?><u><?php echo $id;?></u></legend>
				
				<form id="modifyform">
					<input type="hidden" name="modifyid" value="<?php echo $id;?>">
					<input type="hidden" name="log_user" value="<?=$_SESSION['user']?>" />
					<input type="hidden" name="log_group" value="<?=$_SESSION['usergroup']?>" />
					
				<!-- Custom Tabs -->
				<div role="tabpanel">
				<!--<div class="nav-tabs-custom">-->
					<ul role="tablist" class="nav nav-tabs nav-justified">
						<li class="active"><a href="#tab_1" data-toggle="tab"> <?php $lh->translateText("basic_settings"); ?></a></li>
						<li><a href="#tab_2" data-toggle="tab"><?php $lh->translateText("advance_settings"); ?></a></li>
					</ul>
				   <!-- Tab panes-->
				   <div class="tab-content">
						<!-- BASIC SETTINGS -->
						
						<div id="tab_1" class="tab-pane fade in active">
							<fieldset>
							<div class="form-group mt">
								<label for="server_description" class="col-sm-2 control-label"><?php $lh->translateText("server_description"); ?></label>
								<div class="col-sm-10 mb">
									<input type="text" class="form-control" name="server_description" id="server_description" placeholder="Server Name" value="<?php echo $output->data->server_description;?>" required />
								</div>
							</div>
							<div class="form-group">
								<label for="server_ip" class="col-sm-2 control-label"><?php $lh->translateText("server_ip"); ?></label>
								<div class="col-sm-10 mb">
									<input type="text" class="form-control" name="server_ip" id="server_ip" data-inputmask="'alias': 'ip'" data-mask="" placeholder="Server IP" value="<?php echo $output->data->server_ip;?>" required>
								</div>
							</div>
							<div class="form-group">
								<label for="status" class="col-sm-2 control-label"><?php $lh->translateText("active"); ?></label>
								<div class="col-sm-10 mb">
									<select class="form-control" name="active" id="active">
									<?php
										$active = NULL;
										if($output->data->active == "Y"){
											$active .= '<option value="Y" selected> YES </option>';
										}else{
											$active .= '<option value="Y" > YES </option>';
										}
										
										if($output->data->active == "N" || $output->data->active == NULL){
											$active .= '<option value="N" selected> NO </option>';
										}else{
											$active .= '<option value="N" > NO </option>';
										}
										echo $active;
									?>
									</select>
								</div>
							</div>
							<div class="form-group mt">
								<label class="col-sm-2 control-label" for="user_group"><?php $lh->translateText("user_groups"); ?></label>
								<div class="col-sm-10 mb">
									<select id="user_group" class="form-control select2" name="user_group" style="width:100%;">
										<option value="---ALL---" <?php if($output->data->user_group == "---ALL---")echo "selected";?> >ALL USERGROUPS</option>
										<?php
											for($i=0;$i<count($user_groups->user_group);$i++){
										?>
											<option value="<?php echo $user_groups->user_group[$i];?>" <?php if($user_groups->user_group[$i] == $output->data->user_group){echo "selected";}?>>  <?php echo $user_groups->group_name[$i];?>  </option>
										<?php
											}
										?>
									</select>
								</div>
							</div>
							<div class="form-group">        
                                <label class="col-sm-2 control-label" for="asterisk_version"><?php $lh->translateText("asterisk_version"); ?></label>
                                <div class="col-sm-10 mb">
									<input type="text" class="form-control" name="asterisk_version" id="asterisk_version" maxlength="20" size="20" placeholder="Asterisk Version" value="<?php echo $output->data->asterisk_version; ?>" required>
								</div>
                            </div>
							<div class="form-group">        
                                <label class="col-sm-2 control-label" for="max_vicidial_trunks"><?php $lh->translateText("max_trunks"); ?></label>
                                <div class="col-sm-10 mb">
									<input type="number" class="form-control" name="max_vicidial_trunks" id="max_vicidial_trunks" value="<?php if($output->data->max_vicidial_trunks == "NULL")echo "120"; else echo $output->data->max_vicidial_trunks; ?>" maxlength="4" >
								</div>
                            </div>
							<div class="form-group">        
                                <label class="col-sm-2 control-label" for="outbound_calls_per_second"><?php $lh->translateText("max_call_per_second"); ?></label>
                                <div class="col-sm-10 mb">
									<input type="number" class="form-control" name="outbound_calls_per_second" value="<?php if($output->data->outbound_calls_per_second == "NULL")echo "10"; else echo $output->data->outbound_calls_per_second; ?>" id="outbound_calls_per_second" maxlength="4">
								</div>
                            </div>
							</fieldset>
						</div>
						
						<div id="tab_2" class="tab-pane fade in">
							<fieldset>
							<div class="form-group mt">        
                                <label class="col-sm-2 control-label" for="vicidial_balance_active"><?php $lh->translateText("balance_dialing"); ?></label>
                                <div class="col-sm-10 mb">
									<select name="vicidial_balance_active" id="vicidial_balance_active" class="form-control">
										<option value="N" <?php if($output->data->vicidial_balance_active == "N")echo "selected"; ?> >NO</option>
										<option value="Y" <?php if($output->data->vicidial_balance_active == "Y")echo "selected"; ?> >YES</option>
									</select>
								</div>
                            </div>
							<div class="form-group">        
                                <label class="col-sm-2 control-label" for="local_gmt"><?php $lh->translateText("local_gmt"); ?></label>
                                <div class="col-sm-10 mb">
									<select name="local_gmt" id="local_gmt" class="form-control" required>
										<option value="12.75" <?php if($output->data->local_gmt == "12.75")echo "selected"; ?> > 12.75 </option>
										<option value="12.00" <?php if($output->data->local_gmt == "12.00")echo "selected"; ?> > 12.00 </option>
										<option value="11.00" <?php if($output->data->local_gmt == "11.00")echo "selected"; ?> > 11.00 </option>
										<option value="10.00" <?php if($output->data->local_gmt == "10.00")echo "selected"; ?> > 10.00 </option>
										<option value="9.50" <?php if($output->data->local_gmt == "9.50")echo "selected"; ?> > 9.50 </option>
										<option value="9.00" <?php if($output->data->local_gmt == "9.00")echo "selected"; ?> > 9.00 </option>
										<option value="8.00" <?php if($output->data->local_gmt == "8.00")echo "selected"; ?> > 8.00 </option>
										<option value="7.00" <?php if($output->data->local_gmt == "7.00")echo "selected"; ?> > 7.00 </option>
										<option value="6.50" <?php if($output->data->local_gmt == "6.50")echo "selected"; ?> > 6.50 </option>
										<option value="6.00" <?php if($output->data->local_gmt == "6.00")echo "selected"; ?> > 6.00 </option>
										<option value="5.75" <?php if($output->data->local_gmt == "5.75")echo "selected"; ?> > 5.75 </option>
										<option value="5.50" <?php if($output->data->local_gmt == "5.50")echo "selected"; ?> > 5.50 </option>
										<option value="5.00" <?php if($output->data->local_gmt == "5.00")echo "selected"; ?> > 5.00 </option>
										<option value="4.50" <?php if($output->data->local_gmt == "4.50")echo "selected"; ?> > 4.50 </option>
										<option value="4.00" <?php if($output->data->local_gmt == "4.00")echo "selected"; ?> > 4.00 </option>
										<option value="3.50" <?php if($output->data->local_gmt == "3.50")echo "selected"; ?> > 3.50 </option>
										<option value="3.00" <?php if($output->data->local_gmt == "3.00")echo "selected"; ?> > 3.00 </option>
										<option value="2.00" <?php if($output->data->local_gmt == "2.00")echo "selected"; ?> > 2.00 </option>
										<option value="1.00" <?php if($output->data->local_gmt == "1.00")echo "selected"; ?> > 1.00 </option>
										<option value="0.00" <?php if($output->data->local_gmt == "0.00")echo "selected"; ?> > 0.00 </option>
										<option value="-1.00" <?php if($output->data->local_gmt == "-1.00")echo "selected"; ?> > -1.00 </option>
										<option value="-2.00" <?php if($output->data->local_gmt == "-2.00")echo "selected"; ?> > -2.00 </option>
										<option value="-3.00" <?php if($output->data->local_gmt == "-3.00")echo "selected"; ?> > -3.00 </option>
										<option value="-4.00" <?php if($output->data->local_gmt == "-4.00")echo "selected"; ?> > -4.00 </option>
										<option value="-5.00" <?php if($output->data->local_gmt == "-5.00")echo "selected"; ?> > -5.00 </option>
										<option value="-6.00" <?php if($output->data->local_gmt == "-6.00")echo "selected"; ?> > -6.00 </option>
										<option value="-7.00" <?php if($output->data->local_gmt == "-7.00")echo "selected"; ?> > -7.00 </option>
										<option value="-8.00" <?php if($output->data->local_gmt == "-8.00")echo "selected"; ?> > -8.00 </option>	
										<option value="-9.00" <?php if($output->data->local_gmt == "-9.00")echo "selected"; ?> > -9.00 </option>
										<option value="-10.00" <?php if($output->data->local_gmt == "-10.00")echo "selected"; ?> > -10.00 </option>
										<option value="-11.00" <?php if($output->data->local_gmt == "-11.00")echo "selected"; ?> > -11.00 </option>
										<option value="-12.00" <?php if($output->data->local_gmt == "-12.00")echo "selected"; ?> > -12.00 </option>
									</select>
								</div>
                            </div>
							<div class="form-group mt">        
                                <label class="col-sm-2 control-label" for="generate_vicidial_conf"><?php $lh->translateText("generate_conf_files"); ?></label>
                                <div class="col-sm-10 mb">
									<select name="generate_vicidial_conf" id="generate_vicidial_conf" class="form-control">
										<option value="Y" <?php if($output->data->generate_vicidial_conf == "Y")echo "selected"; ?> >YES</option>
										<option value="N" <?php if($output->data->generate_vicidial_conf == "N")echo "selected"; ?> >NO</option>
									</select>
								</div>
                            </div>
							<div class="form-group mt">        
                                <label class="col-sm-2 control-label" for="rebuild_conf_files"><?php $lh->translateText("rebuild_conf_files"); ?></label>
                                <div class="col-sm-10 mb">
									<select name="rebuild_conf_files" id="rebuild_conf_files" class="form-control">
										<option value="N" <?php if($output->data->rebuild_conf_files == "N")echo "selected"; ?> >NO</option>
										<option value="Y" <?php if($output->data->rebuild_conf_files == "Y")echo "selected"; ?> >YES</option>
									</select>
								</div>
                            </div>
							<div class="form-group mt">        
                                <label class="col-sm-2 control-label" for="rebuild_music_on_hold">Rebuild Music On Hold</label>
                                <div class="col-sm-10 mb">
									<select name="rebuild_music_on_hold" id="rebuild_music_on_hold" class="form-control">
										<option value="N" <?php if($output->data->rebuild_music_on_hold == "N")echo "selected"; ?> >NO</option>
										<option value="Y" <?php if($output->data->rebuild_music_on_hold == "Y")echo "selected"; ?> >YES</option>
									</select>
								</div>
                            </div>
							</fieldset>
						</div>
						
						<fieldset class="footer-buttons">
							<div class="box-footer">
								<div class="col-sm-3 pull-right">
									<a href="settingsservers.php" type="button" class="btn btn-danger"><i class="fa fa-close"></i> Cancel </a>
									<button type="submit" class="btn btn-primary" id="modifyButton" href=""> <span id="update_button"><i class="fa fa-check"></i> Update</span></button>		
								</div>
							</div>
						</fieldset>
					</div><!-- end of tab content -->
					
				</div><!-- tab panel -->
			</form>
						</div><!-- body -->
					</div>
				</section>
					<?php
							}
						}	
                        
					?>
					
				<!-- /.content -->
            </aside><!-- /.right-side -->
        </div><!-- ./wrapper -->

  		<?php print $ui->standardizedThemeJS();?>
		<script src="js/plugins/input-mask/jquery.inputmask.js" type="text/javascript"></script>
		<script src="js/plugins/input-mask/jquery.inputmask.date.extensions.js" type="text/javascript"></script>
		<script src="js/plugins/input-mask/jquery.inputmask.extensions.js" type="text/javascript"></script>
		<!-- Modal Dialogs -->
		<?php include_once "./php/ModalPasswordDialogs.php" ?>

		<script type="text/javascript">
			$(document).ready(function() {
			// disable special characters on Usergroup Name
					$('#server_description').bind('keypress', function (event) {
						var regex = new RegExp("^[a-zA-Z0-9 ]+$");
						var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
						if (!regex.test(key)) {
						   event.preventDefault();
						   return false;
						}
					});
					
			//input mask
				$("[data-mask]").inputmask();
			
			/* initialize select2 */
				$('.select2').select2({
					theme: 'bootstrap'
				});
				
			/** 
			 * Modifies a telephony list
			 */
			$("#modifyform").validate({
				submitHandler: function() {
					//submit the form
						$('#update_button').html("<i class='fa fa-edit'></i> Updating.....");
						$('#modifyButton').attr("disabled", true);

						$.post("./php/ModifyServer.php", //post
						$("#modifyform").serialize(), 
							function(data){
								//if message is sent
								$('#update_button').html("<i class='fa fa-check'></i> Update");
								$('#modifyButton').attr("disabled", false);
								
								if (data == "success") {
									sweetAlert({title: "<?php $lh->translateText("server_modify_success"); ?>",text: "<?php $lh->translateText("server_updated"); ?>",type: "success"},function(){window.location.href = 'settingsservers.php';});
								} else {
									sweetAlert("<?php $lh->translateText("oups"); ?>", "<?php $lh->translateText("something_went_wrong"); ?>"+data, "error");
								}
								//
							});
						return false; //don't let the form refresh the page...
					}					
				});
				
			});
		</script>

		<?php print $ui->getRightSidebar($user->getUserId(), $user->getUserName(), $user->getUserAvatar()); ?>
		<?php print $ui->creamyFooter();?>
    </body>
</html>
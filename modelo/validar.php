<?php
	if(strlen(session_id())<1){
		session_set_cookie_params (['lifetime'=> 0, 'httponly'=>true]);
		session_name('A75fSrCLPf');
		session_start();	
	}
?>
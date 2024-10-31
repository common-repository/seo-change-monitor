if (typeof(cscm_table_list) == 'undefined') cscm_table_list = {};

(function( $ ) {
	'use strict';

//var cscm_table_list = {};

	//setup the admin tabs, very handy compared to different menus
	$( "#cscm-tabs" ).tabs(); 
	
	//setup demo table if provided
	$("#ic-datatable-cscm_list_of_urls").DataTable(
	{
		"stripeClasses": [ 'row1Color', 'row2Color' ]
	}
	);	
/*    $( "#cscm-columns-arranger1, #cscm-columns-arranger2" ).sortable({
      connectWith: ".connected_col_arranger"
    }).disableSelection();
	*/
	//load stuff when all activity settled
	$(document).ajaxStop(function () {
		cscm_on_document_load();
		$(document).unbind("ajaxStop");
	});
	
	function cscm_build_shortcode()
	{
			var selectedCols = $( "#cscm-columns-arranger2" ).sortable( "toArray" );
			var tableID=$("#cscm_table_seq").val();
			if (tableID.length<1) tableID=101;
			var full_short_code="[crawlspider_table id="+tableID+" rows=10 cols="+selectedCols.join(",")+"]";
			$("#cscm_table_shortcode").val(full_short_code);
			console.log(selectedCols);
			console.log(full_short_code);			
	}
	
	function cscm_copy_shortcode()
	{
	    $("#cscm_table_shortcode").select();
		document.execCommand("copy");
		$("#cscm_shortcode_status").text("Shortcode copied to clipboard");
		$("#cscm_shortcode_status").show();
		$("#cscm_shortcode_status").fadeOut(6000);
	}
	
	//build the column dropper UI
    $( "#cscm-columns-arranger1, #cscm-columns-arranger2" ).sortable({
	connectWith: ".connected_col_arranger",
	stop: function( event, ui ) 
		{
			cscm_build_shortcode();
		}
	}).disableSelection();;
	
	$('#cscm_generate_shortcode').on
	(
		{
		 click: function(e) 
			{
				cscm_build_shortcode();
				cscm_copy_shortcode();
			}
		}	
	);

	$('#cscm_copy_shortcode').on
	(
		{
		 click: function(e) 
			{
				cscm_copy_shortcode();
			}
		}	
	);
	//ajax_object.ajax_url is set by wordpress
	
	$('#cscm_start_install').on
	(
		{
		 click: function(e) 
			{
				console.log("button clicked");	
					// We can also pass the url value separately from ajaxurl for front end AJAX implementations
				var data = {
					'action': 'setup_tables',
					'other_data': 'none'      // We pass php values differently!
				};					
				
				jQuery.post(ajax_object.ajax_url, data, function(response) 
				{
					alert('Got this from the server: ' + response);

					console.log("click2");
				});
			}
		}	 
	);
	
	$('#cscm_add_post_urls').on
	(
		{
		 click: function(e) 
			{
				var data = {
					'action': 'get_post_urls',
					'whatever': 'test'      // We pass php values 
				};
				jQuery.post
				(ajax_object.ajax_url, data, 
					function(response) 
					{
						//alert('Got this from the server: ' + response);
						var box_urls=$("#cscm_list_of_urls").val().split(/\r?\n/);
						var combined_url_text=cscm_get_updated_urls(response,box_urls);
						$("#cscm_list_of_urls").val(combined_url_text);
					}
				);
			}
		}	
	);
	
	
	$('#cscm_add_page_urls').on
	(
		{
		 click: function(e) 
			{
				var data = {
					'action': 'get_page_urls',
					'whatever': 'test'      // We pass php values 
				};
				jQuery.post
				(ajax_object.ajax_url, data, 
					function(response) 
					{
						//alert('Got this from the server: ' + response);
						var box_urls=$("#cscm_list_of_urls").val().split(/\r?\n/);
						var combined_url_text=cscm_get_updated_urls(response,box_urls);
						$("#cscm_list_of_urls").val(combined_url_text);
					}
				);
			}
		}	
	);
	
	
	
	$('#cscm_save_settings').on
	(
		{
		 click: function(e) 
			{
				var box_urls=$("#cscm_list_of_urls").val().split(/\r?\n/);
				var each_url,url_scheme;
				for (var i=0;i<box_urls.length;i++)
				{
					each_url=box_urls[i];
					url_scheme=each_url.substr(0,4);
					if (url_scheme.toLowerCase()!="http")
					{
						box_urls[i]="https://"+box_urls[i];
					}
				}
				$("#cscm_list_of_urls").val(box_urls.join("\r\n"));
				
				var form_data =  $("#cscm_settings_form").serialize();
				var data = {
					'action': 'save_option_settings',
					'form_data': form_data      // We pass php values 
				};
				
				jQuery.post
				(ajax_object.ajax_url, data, 
					function(response) 
					{
						console.log('Got this from the server: ' + response);
						//var box=$("#cscm_list_of_urls");
						//box.val(box.val() + "\r\n"+ response);
					}
				);
			}
		}	
	);
	
	
	

	$('#cscm_run_scan_now').on
	(
		{
		 click: function(e) 
			{
				
				
				var data = {
					'action': 'run_scan_now',
					'form_data': 'none'      // We pass php values 
				};
				
				jQuery.post
				(ajax_object.ajax_url, data, 
					function(response) 
					{
						alert('Got this from the server: ' + response);
						//var box=$("#cscm_list_of_urls");
						//box.val(box.val() + "\r\n"+ response);
					}
				);
			}
		}	
	);
	
	jQuery('#cscm_start_install').on
	(
		{

			click: function(e) 
			{
  
			
		
				var es;
		
				var v_urls=icdash_global["script_url"];		 //these urls are passed by the plugin main page that generates the config page  
				var v_current_url=0;	
				var startTime = new Date();
				var endTime=new Date();
				startTask();
				clearLog("Processing...");
				
				function startTask() {
					
					
					$("#result_header").html("Processing script # "+(v_current_url+1));
					addLog("Processing script # "+(v_current_url+1)); 
					
					es = new EventSource(v_urls[v_current_url]);	  
		
					//a message is received
					es.addEventListener('message', function(e) {
					
						var result = JSON.parse( e.data );
						  
						if (e.lastEventId == 'HEADER') 
						{
							$("#result_header").html(result.message);
						}				  
						
						addLog(result.message+' '+result.url);       
						  
						if(e.lastEventId == 'CLOSE') 
						{
							//addLog('Received CLOSE closing');
							es.close();
		
							
							//now start another URL
							v_current_url++;
							if (v_current_url<v_urls.length) 
							{
								startTask();
							}
							else //all scripts installed and complete
							{
								jQuery("#result_header").html("Process complete");
								
								$( "#icdash_dialog" ).dialog({
								  modal: true,
								  buttons: {
									Ok: function() {
									  $( this ).dialog( "close" );
									}
								  }
								});	
								jQuery(".icdash_after_install").show("slow");
								jQuery('#icdash_start_install').hide("slow");
							}	
							
						}
						else if(e.lastEventId =='URL_TRANSFER')
						{
						  es.close();
		
							setTimeout(function () {
								window.location.href= result.url; // the redirect goes here
							},2000); // 5 seconds
							
						}
						else if(e.lastEventId == 'START') {
							addLog('Start');
							
						}
						
						else {
							/*Do Nothing: Ideally this should not happen*/
						}
					});
					  
					es.addEventListener('error', function(e) 
					{
						addLog('Error occurred');
						endTime=new Date();
						var timeDiff = endTime - startTime; //in ms
						// strip the ms
						timeDiff /= 1000;	   
						addLog('The script took longer than '+timeDiff+" seconds. ");
						addLog('Possibly your PHP session has timed out. Please adjust or increase your timeout and restart the install ');
						addLog('<strong>Troubleshoot:</strong> Check log files in the <strong>infocaptor/logs</strong> subdirectory and send them to contact@infocaptor.com');
						console.log(e);
						es.close();
					});
				}
				  
				function stopTask() {
					es.close();
					addLog('Interrupted');
				}
				  
				function addLog(message) {
					var r = document.getElementById('results');
					r.innerHTML += message + '<br>';
					r.scrollTop = r.scrollHeight;
				}		
				function clearLog(message) {
					var r = document.getElementById('results');
					r.innerHTML = message + '<br>';
					r.scrollTop = r.scrollHeight;
				}
		
			}

		}
	);
	
	
	
	function cscm_get_updated_urls(response,box_urls)
	{
		var server_urls=response.split(/\r?\n/);
		var combined_urls=Array.from(new Set(box_urls.concat(server_urls)));
		var combined_url_text=combined_urls.join("\r\n");
		return	combined_url_text;
	}
	
	function cscm_get_table_data(table_name)
	{
		var table_info={};
		table_info['rows'] =cscm_table_list[table_name].rows( { selected: true } ).data();
		table_info['count']=cscm_table_list[table_name].rows( { selected: true } ).count();
		return table_info;
	}
	
	jQuery('#cscm_toggle_url').on
	(
		{
			click: function(e) 
			{
				var table_info=cscm_get_table_data("cscm_list_of_urls_tbl");
				
				var table_row_arry=[];
				for (var i=0;i<table_info['count'];i++)
				{
					table_row_arry[i]=table_info['rows'][i];
				}
				console.log(table_info);
				console.log(table_row_arry);
				var table_data_jsonStr = JSON.stringify(table_row_arry);
				var data = {
					'action': 'toggle_active_inactive_urls_tbl',
					'form_data': table_data_jsonStr
				};
				jQuery.post
				(ajax_object.ajax_url, data, 
					function(response) 
					{
						var table_data_json=response;
						var table_data=JSON.parse(table_data_json);
						cscm_table_list["cscm_list_of_urls_tbl"].clear().rows.add(table_data).draw();
						//alert('Got this from the server: ' + response);
						//var box=$("#cscm_list_of_urls");
						//box.val(box.val() + "\r\n"+ response);
					}
				);				
			}
		}
	);	

	jQuery('#cscm_delete_url').on
	(
		{
			click: function(e) 
			{
				
				var table_info=cscm_get_table_data("cscm_list_of_urls_tbl");				
	
				var table_row_arry=[];
				for (var i=0;i<table_info['count'];i++)
				{
					table_row_arry[i]=table_info['rows'][i];
				}
				
				if (!(confirm('Are you sure you want DELETE selected Urls?')))
				{
				  return;		  
				}				
				console.log(table_info);
				console.log(table_row_arry);
				var table_data_jsonStr = JSON.stringify(table_row_arry);
				var data = {
					'action': 'delete_urls_tbl',
					'form_data': table_data_jsonStr
				};
				jQuery.post
				(ajax_object.ajax_url, data, 
					function(response) 
					{
						var table_data_json=response;
						var table_data=JSON.parse(table_data_json);
						cscm_table_list["cscm_list_of_urls_tbl"].clear().rows.add(table_data).draw();
						//alert('Got this from the server: ' + response);
						//var box=$("#cscm_list_of_urls");
						//box.val(box.val() + "\r\n"+ response);
					}
				);				
			}
		}
	);	
	
	function cscm_list_of_urls_tbl_action(p_action,p_alt_editor,p_modal_selector)
	{
		/*if (p_action=="delete_urls_tbl")
		{	
			
			if (!(confirm('Are you sure you want DELETE selected Urls?')))
			{
			  return;		  
			}	
		}*/
		
		var table_info=cscm_get_table_data("cscm_list_of_urls_tbl");
		
		var table_row_arry=[];
		for (var i=0;i<table_info['count'];i++)
		{
			table_row_arry[i]=table_info['rows'][i];
		}
		console.log(table_info);
		console.log(table_row_arry);
		var table_data_jsonStr = JSON.stringify(table_row_arry);
		var data = {
			'action': p_action,
			'form_data': table_data_jsonStr
		};
		jQuery.post
		(ajax_object.ajax_url, data, 
			function(response) 
			{
				var table_data_json=response;
				var table_data=JSON.parse(table_data_json);
				cscm_table_list["cscm_list_of_urls_tbl"].clear().rows.add(table_data).draw();
				p_alt_editor.internalCloseDialog(p_modal_selector);
				//alert('Got this from the server: ' + response);
				//var box=$("#cscm_list_of_urls");
				//box.val(box.val() + "\r\n"+ response);
			}
		);				
		
	}
	
	function cscm_on_document_load()
	{
		var data = {
			'action': 'display_list_of_urls_tbl',
			'form_data': 'none'      // We pass php values 
		};
		
		jQuery.post
		(ajax_object.ajax_url, data, 
			function(response) 
			{
				$("#cscm_list_of_urls_tbl_parent").html(response);
				var column_def=cscm_get_column_def('table_name');
				cscm_table_list["cscm_list_of_urls_tbl"]=$("#cscm_list_of_urls_tbl").DataTable(
					{
						dom: 'Bfrtip',
						select: true,
						columns:column_def,
						responsive: true,
						altEditor: true,     // Enable altEditor
						buttons: [
							{
								text: 'Add',
								name: 'add'        // do not change name
							},
							{
								extend: 'selected', // Bind to Selected row
								text: 'Edit',
								name: 'edit'        // do not change name
							},
							{
								extend: 'selected', // Bind to Selected row
								text: 'Delete',
								name: 'delete'      // do not change name
							},
							{
								text: 'Refresh',
								name: 'refresh'      // do not change name
							},
							{
								text: 'Toggle Status',
								  action: function ( e, dt, node, config ) 
										{
											alert( 'Button activated' );
										}
							}
						],
						onDeleteRow: function(datatable, rowdata, success, error) 
						{
							cscm_list_of_urls_tbl_action('delete_urls_tbl',this,datatable.modal_selector);
							return;
						},	
						onEditRow: function(datatable, rowdata, success, error) 
						{
							console.log(rowdata);
							var row = [];
							var rowObj={};
							for(var i in rowdata)
							{
								if (!isNaN(i)) row.push(rowdata [i]);
								//if (!isNaN(i)) rowObj[i]=rowdata[i];
							}	
							var row_arry=[row];
							//var rowDataObj={};
							//rowDataObj['data']=rowObj;
							var table_data_jsonStr = JSON.stringify(row_arry);
							var data = {
								'action': 'toggle_active_inactive_urls_tbl',
								'form_data': table_data_jsonStr
							};
							console.log(ajax_object.ajax_url);
							console.log(datatable.modal_selector);

							var cscm_alteditor=this;
							cscm_list_of_urls_tbl_action('toggle_active_inactive_urls_tbl',this,datatable.modal_selector);
							return;
							
							jQuery.post
							(ajax_object.ajax_url, data, 
								function(response) 
								{
									var table_data_json=response;
									var table_data=JSON.parse(table_data_json);
									cscm_table_list["cscm_list_of_urls_tbl"].clear().rows.add(table_data).draw();

									cscm_alteditor.internalCloseDialog(datatable.modal_selector);

									//alert('Got this from the server: ' + response);
									//var box=$("#cscm_list_of_urls");
									//box.val(box.val() + "\r\n"+ response);
								}
							);
				            /*$.ajax({
								// a tipycal url would be /{id} with type='POST'
								url: "admin-ajax.php",
								type: 'POST',
								data: {"row_data":rowObj},
								success: success,
								error: error
							});*/
							
						},			
						 /*"createdRow": function( row, data, dataIndex ) 
										{
											if ( data[3] == "N" ) 
											{   
												//$(row).addClass('ui-state-error');
												data[3]="<span style='color:red;'>"+data[3]+"</span>";
												$('td:eq(3)', row).html( '<span style="color:red;fontWeight:bold;">N</span>' );

											}
										},*/
						 "rowCallback": function( row, data, dataIndex ) 
										{
											if ( data[3] == "N" ) 
											{   
												//$(row).addClass('ui-state-error');
												//data[3]="<span style='color:red;'>"+data[3]+"</span>";
												$('td:eq(3)', row).html( '<span style="color:red;font-style:bold;">Paused</span>' );
												$('td:eq(1)', row).html( '<span style="color:red;font-style:italic;">'+data[1]+'</span>' );

											}
										}
					}
				);

				/*$('#cscm_list_of_urls_tbl tbody').on( 'click', 'tr', function () {
						if ( $(this).hasClass('selected') ) {
							$(this).removeClass('selected');
						}
						else {
							table.$('tr.selected').removeClass('selected');
							$(this).addClass('selected');
						}
					} );		*/		
			}
		);
	}
	
	function cscm_get_column_def(table_name)	
	{
		var tableColumnDefs = {};
		tableColumnDefs['cscm_list_of_urls_tbl']=[
        {readonly: true},
		{readonly: true},
		{readonly: true},
		{},
		{},
		{readonly: true,type:'hidden'},
		{readonly: true,type:'hidden'}
		];
		return tableColumnDefs['cscm_list_of_urls_tbl'];
	}

	
})( jQuery );

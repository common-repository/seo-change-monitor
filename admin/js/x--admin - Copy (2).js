if (typeof(cscm_table_list) == 'undefined') cscm_table_list = {};
var cscm_latest_list_of_urls;
var cscm_alert_list;

(function( $ ) {
	'use strict';

//var cscm_table_list = {};

    var alert_data = [];
	alert_data[0]={"header":"A Tag Changed: Keywords replaced","details":"Call <del>111-111-1111</del><ins>0-11-111-111-1111</ins> >>   href = https://www.crawlspider.com/#  role = button"};
	alert_data[1]={"header":"A Tag Changed: Keywords replaced","details":"Watch and <del>Protect</del><ins>Safeguard</ins> your Organic Traffic >>   href = https://www.crawlspider.com/watch-and-protect-your-organic-traffic/  title = Watch and Protect your Organic Traffic"};
	alert_data[2]={"header":"A Tag Changed: Keywords replaced","details":"A Guide to 5xx server<del>  errors andhow</del><ins> how</ins> to fix them >>   href = https://www.crawlspider.com/5xx-server-errors-and-how-to-fix-them/  title = A Guide to 5xx server errors and how to fix them"};
	alert_data[3]={"header":"H2 Tag Changed: Keywords replaced","details":"<del>Servi   ces</del><ins>Story</ins>"};
	alert_data[4]={"header":"H2 Tag Changed: Keywords replaced","details":"<del>Locations</del><ins>Services and Solutions"};
	alert_data[5]={"header":"H2 Tag Changed: Keywords added","details":"Gallery<ins> Display</ins>"};
	alert_data[6]={"header":"H2 Tag Changed: Keywords replaced","details":"<del>SchultheisRoofing</del><ins>Sc hul thei s   Roofing</ins>"};
	alert_data[7]={"header":"H2 Tag Changed: Keywords replaced","details":"We service all your <del>roofing</del><ins>roof  ing</ins> needs"};

	//setup the admin tabs, very handy compared to different menus
	$( "#cscm-tabs" ).tabs(
		{ activate: function(event ,ui)
			{
				console.log(ui.newPanel.attr('id'));
				if (ui.newPanel.attr('id')=="checks-tab")
				{
					cscm_update_urls_for_checks();
				}
				else if (ui.newPanel.attr('id')=="alerts-tab")
				{
					/*
					console.log("in alerts");
					for (var i=0;i<alert_data.length;i++)
					{
						var card_template=`
							<div class="card">
								<h5 class="card-header" role="tab" id="alert_heading_${i}">
									<a  class="collapsed d-block" data-toggle="collapse" data-parent="#accordion" href="#alert_text_${i}" aria-expanded="true" aria-controls="alert_text_${i}" >
										<i class="fa fa-chevron-down pull-right"></i> ${alert_data[i].header}
									</a>
								</h5>

								<div id="alert_text_${i}" class="collapse" role="tabpanel" aria-labelledby="alert_heading_${i}">
									<div class="card-body">
									${alert_data[i].details}
									</div>
								</div>
							</div>					
`;

						//console.log(card_template);
						var item = $(card_template).hide().delay(1000*(i+1)).slideUp(500).fadeIn(500);
									
						$("#accordion1").prepend(item);		
						


						
					}*/
					cscm_get_latest_alerts();
				}	

			} 
		}	
	); 
	
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
					console.log('Got this from the server: ' + response);

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
						cscm_list_of_urls_tbl_action('refresh',null,null,null);
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
	
/*	
    $( "#checks-tab" ).tabs(
		{ 
			activate: function(event ,ui)
			{
				cscm_get_latest_urls();
            } 
		});
*/
	
	function cscm_get_latest_urls()
	{
		if (typeof(cscm_latest_list_of_urls) == 'undefined') 
		{
			cscm_latest_list_of_urls = {};
			var data = {
				'action': 'edit_list_of_urls_tbl',
				'form_data': 'none',
				'form_action':'list_active_urls'
			};
			jQuery.post
			(ajax_object.ajax_url, data, 
				function(response) 
				{
					var table_data_json=response;
					var table_data=JSON.parse(table_data_json);
					cscm_latest_list_of_urls['list']=table_data;
					
					var l_altEditor = $( "#cscm_checks_tbl" )[0].altEditor;
					var url_field = $(l_altEditor.modal_selector).find('#1'); //we have sequential ids 
					var url_options={};
					for (var i=0;i<table_data.length;i++)
					{
						url_options[table_data[i][0]]=table_data[i][1];
					}
					l_altEditor.customOptions[1]=url_options;
//					l_altEditor.reloadOptions(url_field, url_options);
				}
			);	
		}	
		
	}


	function cscm_get_latest_alerts()
	{

		var data = {
			'action': 'get_list_of_alerts',
			'form_data': 'none'
		};
		jQuery.post
		(ajax_object.ajax_url, data, 
			function(response) 
			{
				var table_data_json=response;
				var table_data=JSON.parse(table_data_json);
				console.log(table_data);
				//cscm_latest_list_of_urls['list']=table_data;
				var alert_html=cscm_build_full_card_stack(table_data);
				$("#change_alert_box").append(alert_html);

			}
		);	
		
		
	}
	
	function cscm_update_latest_urls(table_data)
	{
		/*
		**When the list of urls table is refreshed, this will update the select options for any other screens
		with a list of active urls
		*/
		cscm_latest_list_of_urls = {};
		cscm_latest_list_of_urls['list']=table_data;

		
	}

	function cscm_update_urls_for_checks()
	{
		/*
		**When the list of urls table is refreshed, this will update the select options for any other screens
		with a list of active urls
		*/
		
		var table_data=cscm_latest_list_of_urls['list'];
		var url_options={};
		
		for (var i=0;i<table_data.length;i++)
		{
			url_options[table_data[i][0]]=table_data[i][1];
		}	
		var l_altEditor = $( "#cscm_checks_tbl" )[0].altEditor;
		var url_field = $(l_altEditor.modal_selector).find('#1'); //we have sequential ids 
		l_altEditor.customOptions[1]=url_options;
		
	}
	
	function cscm_get_updated_urls(response,box_urls)
	{
		var server_urls=response.split(/\r?\n/);
		var combined_urls=Array.from(new Set(box_urls.concat(server_urls)));
		var combined_url_text=combined_urls.join("\r\n");
		return	combined_url_text;
	}
	
	function cscm_get_table_data(table_name,p_selector_obj)
	{
		var table_info={};
		//table_info['rows'] =cscm_table_list[table_name].rows( { selected: true } ).data();
		table_info['rows'] =cscm_table_list[table_name].rows( p_selector_obj ).data();
		table_info['count']=cscm_table_list[table_name].rows( p_selector_obj ).count();
		return table_info;
	}
	
	/*
	jQuery('#cscm_toggle_url').on
	(
		{
			click: function(e) 
			{
				var table_info=cscm_get_table_data("cscm_list_of_urls_tbl", { selected: true } );
				
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
	*/
	
/*
	jQuery('#cscm_delete_url').on
	(
		{
			click: function(e) 
			{
				
				var table_info=cscm_get_table_data("cscm_list_of_urls_tbl", { selected: true } );				
	
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
	*/
	function cscm_on_document_load()
	{
		cscm_list_of_urls_tbl_load();
		cscm_checks_tbl_load();
	}	
	
	
	function cscm_list_of_urls_tbl_action(p_action,p_selector_obj,p_rowdata,p_alt_editor,p_modal_selector)
	{
		/*if (p_action=="delete_urls_tbl")
		{	
			
			if (!(confirm('Are you sure you want DELETE selected Urls?')))
			{
			  return;		  
			}	
		}*/
		console.log("in tbl action");
		console.log(p_action);
		console.log(p_rowdata);
		
		var table_data_jsonStr;
		var table_row_arry=[];
		///add data
		if (p_action=="add"||p_action=="edit")
		{
			var row = [];
			var rowObj={};
			for(var i in p_rowdata)
			{
				if (!isNaN(i)) row.push(p_rowdata [i]);
				
			}	
			table_row_arry=[row];

		/////add
		}
		else
		{
			
			var table_info=cscm_get_table_data("cscm_list_of_urls_tbl", p_selector_obj );
			
			
			for (var i=0;i<table_info['count'];i++)
			{
				table_row_arry[i]=table_info['rows'][i];
			}
			console.log(table_info);
			console.log(table_row_arry);

		}
		
		table_data_jsonStr = JSON.stringify(table_row_arry);
		var data = {
			'action': 'edit_list_of_urls_tbl',
			'form_data': table_data_jsonStr,
			'form_action':p_action
		};
		jQuery.post
		(ajax_object.ajax_url, data, 
			function(response) 
			{
				var table_data_json=response;
				var table_data=JSON.parse(table_data_json);
				cscm_table_list["cscm_list_of_urls_tbl"].clear().rows.add(table_data).draw();
				if (!(p_modal_selector==undefined))	p_alt_editor.internalCloseDialog(p_modal_selector);
				
				//alert('Got this from the server: ' + response);
				//var box=$("#cscm_list_of_urls");
				//box.val(box.val() + "\r\n"+ response);
			}
		);				
		
	}
	
	

	
	function cscm_list_of_urls_tbl_load()
	{
		var data = {
			'action': 'display_list_of_urls_tbl',
			'form_data': 'none'      // We pass php values 
		};
		
		jQuery.post
		(ajax_object.ajax_url, data, 
			function(response) 
			{
				//$("#cscm_list_of_urls_tbl_parent").html(response);
				var responseObj=JSON.parse(response);
				$("#cscm_list_of_urls_tbl_parent").html(responseObj.table_html);		
				cscm_update_latest_urls(responseObj.dataset);
				var column_def=cscm_get_list_of_urls_column_def('table_name');
				cscm_table_list["cscm_list_of_urls_tbl"]=$("#cscm_list_of_urls_tbl").DataTable(
					{
						dom: 'Bfrtip',
						select: true,
						columns:column_def,
						responsive: true,
						iDisplayLength: 20,
						altEditor:{
						language:{'add':{'title':'Add URL'},'edit':{'title':'Edit URL'},'delete':{'title':'delete URL'}}
						},
						buttons: [
							{
								text: 'Add',			
								className: 'btn  btn-sm btn-primary btn_add_main',
								name: 'add'        // do not change name
							},
							{
								className: 'btn btn-sm  btn-secondary btn_edit_main',
								extend: 'selected', // Bind to Selected row
								text: 'Edit',
								name: 'edit'        // do not change name
							},
							{
								className: 'btn btn-sm  btn-danger btn_delete_main',
								extend: 'selected', // Bind to Selected row
								text: 'Delete',
								name: 'delete'      // do not change name
							},
							{
								className: 'btn  btn-sm btn-info btn_refresh_main',
								text: 'Refresh',
								  action: function ( e, dt, node, config ) 
										{
											cscm_list_of_urls_tbl_action('refresh',null,null,null,null);
										}
								
							},
							{
								className: 'btn btn-sm btn-secondary btn_toggle_main',
								extend: 'selected',
								text: 'Toggle Status',
								  action: function ( e, dt, node, config ) 
										{
											cscm_list_of_urls_tbl_action('toggle_status',{ selected: true },null,null,null);
										}
							}
						],
						onDeleteRow: function(datatable, rowdata, success, error) 
						{
							//setTimeout(that._openDeleteModal(), 1000);
							//row selection is needed for deletion
							cscm_list_of_urls_tbl_action('delete',{ selected: true },rowdata,this,datatable.modal_selector);
							
						},	
						onEditRow: function(datatable, rowdata, success, error) 
						{
							console.log("on edit row");
							//for edit the modal will send the updated values and so row selection is not needed
							cscm_list_of_urls_tbl_action('edit',null,rowdata,this,datatable.modal_selector);
							//cscm_list_of_urls_tbl_action('edit',null,rowdata,this,datatable.modal_selector);
							
						},			
						onAddRow: function(datatable, rowdata, success, error) 
						{
							
							cscm_list_of_urls_tbl_action('add',null,rowdata,this,datatable.modal_selector);
							
						},
						 "rowCallback": function( row, data, dataIndex ) 
										{
											if ( data[3] == "N" ) 
											{   
												//$(row).addClass('ui-state-error');
												//data[3]="<span style='color:red;'>"+data[3]+"</span>";
												$('td:eq(3)', row).html( '<span class="badge badge-warning font90">Paused</span>' );
												$('td:eq(1)', row).html( '<span style="font-style:italic;">'+data[1]+'</span>' );

											}
											else
											{
												$('td:eq(3)', row).html( '<span class="badge badge-info font100"><i class="bi bi-check-lg"></i></i></span>' );
											}
											if (data[7]!=undefined && data[7]!="complete") $('td:eq(7)', row).html( '<span class="badge badge-danger font90">'+data[7]+'</span>' );
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
			  // Edit
			   //$(document).on('click', "[id^='example'] tbody ", 'tr', function ()
			  $("#cscm_list_of_urls_tbl").on('click', ".cscm_edit_btn", 'tr', function (x) 
			  {
				//var tableID = $(this).closest('table').attr('id');    // id of the table
				$(this).closest('tr').addClass("selected");	
				cscm_table_list["cscm_list_of_urls_tbl"].row( $(this).closest('tr') ).select();
				//var that = $( "#cscm_list_of_urls_tbl" )[0].altEditor;
				console.log("inside row button action");
				//var data = cscm_table_list["cscm_list_of_urls_tbl"].row( $(this).parents('tr') ).data();
				//console.log(data);
				$(".btn_edit_main").trigger("click");
				var that = $( "#cscm_list_of_urls_tbl" )[0].altEditor;
				$('#altEditor-delete-form-' + that.random_id)
							.off('submit')
							.on('submit', function (e) {
								e.preventDefault();
								e.stopPropagation();
								that._editRowData();
							});
				

			  });

			  // Delete
			  $("#cscm_list_of_urls_tbl").on('click', ".cscm_delete_btn", 'tr', function (x) 
			  {
				 $(this).closest('tr').addClass("selected");	
				cscm_table_list["cscm_list_of_urls_tbl"].row( $(this).closest('tr') ).select();
				//var that = $( "#cscm_list_of_urls_tbl" )[0].altEditor;
				console.log("inside row button action");
				//var data = cscm_table_list["cscm_list_of_urls_tbl"].row( $(this).parents('tr') ).data();
				//console.log(data);
				
				$(".btn_delete_main").trigger("click");
				var that = $( "#cscm_list_of_urls_tbl" )[0].altEditor;
				$('#altEditor-delete-form-' + that.random_id)
							.off('submit')
							.on('submit', function (e) {
								e.preventDefault();
								e.stopPropagation();
								that._deleteRow();
							});
				x.stopPropagation(); //avoid open "Edit" dialog				
				 /* 
				//var tableID = $(this).closest('table').attr('id');    // id of the table
				$(this).parents('tr').addClass("selected");
				cscm_table_list["cscm_list_of_urls_tbl"].row( $(this).parents('tr') ).select();
				var that = $( "#cscm_list_of_urls_tbl" )[0].altEditor;
				console.log("inside row button action");
				//var data = cscm_table_list["cscm_list_of_urls_tbl"].row( $(this).parents('tr') ).data();
				console.log(data);
				
				//that._openDeleteModal();
				//that._openDeleteModal();
				*/


			  });

			  // Toggle
			  $("#cscm_list_of_urls_tbl").on('click', ".cscm_toggle_btn", 'tr', function (x) 
			  {
				$(this).closest('tr').addClass("selected");	
				cscm_table_list["cscm_list_of_urls_tbl"].row( $(this).closest('tr') ).select();
				$(".btn_toggle_main").trigger("click");

			  });
					
			}
		);
		
		
		
		
	}
	
	function cscm_get_list_of_urls_column_def(table_name)	
	{
		//if the field is readonly for add and edit then simply add readonly:true
		//for separate rules for Add and Edit use, editRowBtn and addRowBtn keys
		var tableColumnDefs = {};
		tableColumnDefs['cscm_list_of_urls_tbl']=[
        {readonly: true,hidden:{'editRowBtn':false,'addRowBtn':true}},
		{'type':'url','width':'30%',readonly: {'editRowBtn':true,'addRowBtn':false},required: true},
		{readonly: true,hidden:{'editRowBtn':false,'addRowBtn':true}},
		{'type':'select','options':{ 'Y' : 'Y', 'N' : 'N' },hidden:{'editRowBtn':false,'addRowBtn':true}},
		{'type':'email'},
		{readonly: true,type:'hidden'},
		{readonly: true,type:'hidden',width:'5%'},
		{readonly: true,type:'hidden'},
		{readonly: true,type:'hidden', "targets": -1, "data": null,"defaultContent": "<button type='button' title='Edit' class='cscm_edit_btn btn btn-smxl btn-primary' ><i class='bi bi-pencil-fill'></i></button> <button type='button' title='Delete' class='cscm_delete_btn btn btn-smxl btn-danger' ><i class='bi bi-x-circle-fill'></i></button> <button type='button' title='Toggle Status' class='cscm_toggle_btn btn btn-smxl btn-info' ><i class='bi bi-toggle-on'></i></button>"}
		];
		
		
		return tableColumnDefs['cscm_list_of_urls_tbl'];
	}

////Checks Table routines

	
	function cscm_checks_tbl_action(p_table_html_id,p_action,p_selector_obj,p_rowdata,p_alt_editor,p_modal_selector)
	{
		/*if (p_action=="delete_urls_tbl")
		{	
			
			if (!(confirm('Are you sure you want DELETE selected Urls?')))
			{
			  return;		  
			}	
		}*/
		console.log("in tbl action");
		console.log(p_action);
		console.log(p_rowdata);
		
		var table_data_jsonStr;
		var table_row_arry=[];
		
		if (p_action=="edit"||p_action=="delete")
		{
			
		}
		///add data
		if (p_action=="add"||p_action=="edit")
		{
			var row = [];
			var rowObj={};
			for(var i in p_rowdata)
			{
				if (!isNaN(i)) row.push(p_rowdata [i]);
				
			}	
			table_row_arry=[row];

		/////add
		}
		else
		{
			
			var table_info=cscm_get_table_data(p_table_html_id, p_selector_obj );
			
			
			for (var i=0;i<table_info['count'];i++)
			{
				table_row_arry[i]=table_info['rows'][i];
			}
			console.log(table_info);
			console.log(table_row_arry);

		}
		
		table_data_jsonStr = JSON.stringify(table_row_arry);
		var data = {
			'action': 'edit_checks_tbl',
			'form_data': table_data_jsonStr,
			'form_action':p_action
		};
		jQuery.post
		(ajax_object.ajax_url, data, 
			function(response) 
			{
				var table_data_json=response;
				var table_data=JSON.parse(table_data_json);
				cscm_table_list[p_table_html_id].clear().rows.add(table_data).draw();
				if (!(p_modal_selector==undefined))	p_alt_editor.internalCloseDialog(p_modal_selector);
				
				//alert('Got this from the server: ' + response);
				//var box=$("#cscm_checks");
				//box.val(box.val() + "\r\n"+ response);
			}
		);				
		
	}
	

	function cscm_default_checks_tbl_action(p_table_html_id,p_action,p_selector_obj,p_rowdata,p_alt_editor,p_modal_selector)
	{
		/*if (p_action=="delete_urls_tbl")
		{	
			
			if (!(confirm('Are you sure you want DELETE selected Urls?')))
			{
			  return;		  
			}	
		}*/
		console.log("in tbl action");
		console.log(p_action);
		console.log(p_rowdata);
		
		var table_data_jsonStr;
		var table_row_arry=[];
		
		if (p_action=="edit"||p_action=="delete")
		{
			
		}
		///add data
		if (p_action=="add"||p_action=="edit")
		{
			var row = [];
			var rowObj={};
			for(var i in p_rowdata)
			{
				if (!isNaN(i)) row.push(p_rowdata [i]);
				
			}	
			table_row_arry=[row];

		/////add
		}
		else
		{
			
			var table_info=cscm_get_table_data(p_table_html_id, p_selector_obj );
			
			
			for (var i=0;i<table_info['count'];i++)
			{
				table_row_arry[i]=table_info['rows'][i];
			}
			console.log(table_info);
			console.log(table_row_arry);

		}
		
		table_data_jsonStr = JSON.stringify(table_row_arry);
		var data = {
			'action': 'edit_checks_tbl',
			'form_data': table_data_jsonStr,
			'form_action':'default_checks_'+p_action
		};
		jQuery.post
		(ajax_object.ajax_url, data, 
			function(response) 
			{
				var table_data_json=response;
				var table_data=JSON.parse(table_data_json);
				cscm_table_list[p_table_html_id].clear().rows.add(table_data).draw();
				if (!(p_modal_selector==undefined))	p_alt_editor.internalCloseDialog(p_modal_selector);
				
				//alert('Got this from the server: ' + response);
				//var box=$("#cscm_checks");
				//box.val(box.val() + "\r\n"+ response);
			}
		);				
		
	}	

	
	function cscm_checks_tbl_load()
	{
		var data = {
			'action': 'display_checks_tbl',
			'form_data': 'none'      // We pass php values 
		};
		
		jQuery.post
		(ajax_object.ajax_url, data, 
			function(response) 
			{
				var responseObj=JSON.parse(response);
				$("#cscm_checks_tbl_parent").html(responseObj.table_html);
				$("#cscm_default_checks_tbl_parent").html(responseObj.table_html2);
				cscm_create_checks_tbl();
				cscm_create_default_checks_tbl();
					
			} //function end
		);
		
		
		
	}

	function cscm_create_checks_tbl()
	{
				var column_def=cscm_get_checks_column_def('table_name');
				cscm_table_list["cscm_checks_tbl"]=$("#cscm_checks_tbl").DataTable(
					{
						dom: 'Bfrtip',
						select: true,
						columns:column_def,
						responsive: true,
						iDisplayLength: 20,
						altEditor:{
						language:{'add':{'title':'Add URL'},'edit':{'title':'Edit URL'},'delete':{'title':'delete URL'}}
						},
						buttons: [
							{
								text: 'Add',			
								className: 'btn  btn-sm btn-primary btn_add_main',
								name: 'add'        // do not change name
							},
							{
								className: 'btn btn-sm  btn-secondary btn_edit_main',
								extend: 'selected', // Bind to Selected row
								text: 'Edit',
								name: 'edit'        // do not change name
							},
							{
								className: 'btn btn-sm  btn-danger btn_delete_main',
								extend: 'selected', // Bind to Selected row
								text: 'Delete',
								name: 'delete'      // do not change name
							},
							{
								className: 'btn  btn-sm btn-info btn_refresh_main',
								text: 'Refresh',
								  action: function ( e, dt, node, config ) 
										{
											cscm_checks_tbl_action('cscm_checks_tbl','refresh',null,null,null,null);
										}
								
							},
							{
								className: 'btn btn-sm btn-secondary btn_toggle_main',
								extend: 'selected',
								text: 'Toggle Status',
								  action: function ( e, dt, node, config ) 
										{
											cscm_checks_tbl_action('cscm_checks_tbl','toggle_status',{ selected: true },null,null,null);
										}
							}
						],
						onDeleteRow: function(datatable, rowdata, success, error) 
						{
							//setTimeout(that._openDeleteModal(), 1000);
							//row selection is needed for deletion
							cscm_checks_tbl_action('cscm_checks_tbl','delete',{ selected: true },rowdata,this,datatable.modal_selector);
							
						},	
						onEditRow: function(datatable, rowdata, success, error) 
						{
							console.log("on edit row");
							//for edit the modal will send the updated values and so row selection is not needed
							cscm_checks_tbl_action('cscm_checks_tbl','edit',null,rowdata,this,datatable.modal_selector);
							//cscm_checks_tbl_action('edit',null,rowdata,this,datatable.modal_selector);
							
						},			
						onAddRow: function(datatable, rowdata, success, error) 
						{
							
							cscm_checks_tbl_action('cscm_checks_tbl','add',null,rowdata,this,datatable.modal_selector);
							
						},
						 "rowCallback": function( row, data, dataIndex ) 
										{
											if ( data[1] == "-1" ) 
											{   
												//$(row).addClass('ui-state-error');
												//data[3]="<span style='color:red;'>"+data[3]+"</span>";
												$('td:eq(1)', row).html( '<span class="badge badge-primary font90">ALL URLs</span>' );
												//$('td:eq(1)', row).html( '<span style="color:red;font-style:italic;">'+data[1]+'</span>' );

											}
											if ( data[5] == "Y" )
											{	
												$('td:eq(5)', row).html( '<span class="badge badge-info font100"><i class="bi bi-check-lg"></i></i></span>' );
											}
											else	
											{   
												$('td:eq(5)', row).html( '<span class="badge badge-warning font90">Paused</span>' );								
											}
											//if (data[7]!=undefined && data[7]!="complete") $('td:eq(7)', row).html( '<span class="badge badge-danger font90">'+data[7]+'</span>' );
										}
					}
				);

				/*$('#cscm_checks_tbl tbody').on( 'click', 'tr', function () {
						if ( $(this).hasClass('selected') ) {
							$(this).removeClass('selected');
						}
						else {
							table.$('tr.selected').removeClass('selected');
							$(this).addClass('selected');
						}
					} );		*/	
			  // Edit
			   //$(document).on('click', "[id^='example'] tbody ", 'tr', function ()
			  $("#cscm_checks_tbl").on('click', ".cscm_edit_btn", 'tr', function (x) 
			  {
				//var tableID = $(this).closest('table').attr('id');    // id of the table
				$(this).closest('tr').addClass("selected");	
				cscm_table_list["cscm_checks_tbl"].row( $(this).closest('tr') ).select();
				//var that = $( "#cscm_checks_tbl" )[0].altEditor;
				console.log("inside row button action");
				//var data = cscm_table_list["cscm_checks_tbl"].row( $(this).parents('tr') ).data();
				//console.log(data);
				$(".btn_edit_main").trigger("click");
				var that = $( "#cscm_checks_tbl" )[0].altEditor;
				$('#altEditor-delete-form-' + that.random_id)
							.off('submit')
							.on('submit', function (e) {
								e.preventDefault();
								e.stopPropagation();
								that._editRowData();
							});
				

			  });

			  // Delete
			  $("#cscm_checks_tbl").on('click', ".cscm_delete_btn", 'tr', function (x) 
			  {
				 $(this).closest('tr').addClass("selected");	
				cscm_table_list["cscm_checks_tbl"].row( $(this).closest('tr') ).select();
				//var that = $( "#cscm_checks_tbl" )[0].altEditor;
				console.log("inside row button action");
				//var data = cscm_table_list["cscm_checks_tbl"].row( $(this).parents('tr') ).data();
				//console.log(data);
				
				$(".btn_delete_main").trigger("click");
				var that = $( "#cscm_checks_tbl" )[0].altEditor;
				$('#altEditor-delete-form-' + that.random_id)
							.off('submit')
							.on('submit', function (e) {
								e.preventDefault();
								e.stopPropagation();
								that._deleteRow();
							});
				x.stopPropagation(); //avoid open "Edit" dialog				
				 /* 
				//var tableID = $(this).closest('table').attr('id');    // id of the table
				$(this).parents('tr').addClass("selected");
				cscm_table_list["cscm_checks_tbl"].row( $(this).parents('tr') ).select();
				var that = $( "#cscm_checks_tbl" )[0].altEditor;
				console.log("inside row button action");
				//var data = cscm_table_list["cscm_checks_tbl"].row( $(this).parents('tr') ).data();
				console.log(data);
				
				//that._openDeleteModal();
				//that._openDeleteModal();
				*/


			  });	

			  // Toggle
			  $("#cscm_checks_tbl").on('click', ".cscm_toggle_btn", 'tr', function (x) 
			  {
				$(this).closest('tr').addClass("selected");	
				cscm_table_list["cscm_checks_tbl"].row( $(this).closest('tr') ).select();
				$(".btn_toggle_main").trigger("click");

			  });			  
	}
	
	function cscm_create_default_checks_tbl()
	{
		cscm_table_list["cscm_default_checks_tbl"]=$("#cscm_default_checks_tbl").DataTable
		(
					{
						dom: 'Bfrtip',
						select: true,
						responsive: true,
						iDisplayLength: 20,
						altEditor:{
						language:{'add':{'title':'Add URL'},'edit':{'title':'Edit URL'},'delete':{'title':'delete URL'}}
						},
						buttons: [
							{
								className: 'btn btn-sm btn-secondary btn_toggle_main',
								extend: 'selected',
								text: 'Toggle Status',
								  action: function ( e, dt, node, config ) 
										{
											cscm_default_checks_tbl_action('cscm_default_checks_tbl','toggle_status',{ selected: true },null,null,null);
										}
							}
						],
						 "rowCallback": function( row, data, dataIndex ) 
										{
											if ( data[1] == "-1" ) 
											{   
												//$(row).addClass('ui-state-error');
												//data[3]="<span style='color:red;'>"+data[3]+"</span>";
												$('td:eq(1)', row).html( '<span class="badge badge-primary font90">ALL URLs</span>' );
												//$('td:eq(1)', row).html( '<span style="color:red;font-style:italic;">'+data[1]+'</span>' );

											}
											if ( data[4] == "Y" )
											{	
												$('td:eq(4)', row).html( '<span class="badge badge-info font100"><i class="bi bi-check-lg"></i></i></span>' );
											}
											else	
											{   
												$('td:eq(4)', row).html( '<span class="badge badge-warning font90">Paused</span>' );								
											}
											//if (data[7]!=undefined && data[7]!="complete") $('td:eq(7)', row).html( '<span class="badge badge-danger font90">'+data[7]+'</span>' );
										}
					}		
		);
	}
	
	function cscm_get_checks_column_def(table_name)	
	{
		
///
		var tableColumnDefs = {};
		tableColumnDefs['cscm_checks_tbl']=[
        {readonly: true},
		{'type':'select','options':{},'width':'30%',readonly: {'editRowBtn':true,'addRowBtn':false}},
		{readonly: true,type:'hidden'},
		{'type':'select','options':{'tag':'Tag','class':'Class','xpath':'XPath','regex':'Regex','string':'Search String'},readonly: false,hidden:{'editRowBtn':false,'addRowBtn':false}},
		
		{'type':'textarea',readonly: {'editRowBtn':false,'addRowBtn':false},required: true},
		{'type':'select','options':{ 'Y' : 'Y', 'N' : 'N' }},
		{'type':'email'},
		{readonly: false,type:'hidden', "targets": -1, "data": null,"defaultContent": "<button type='button' title='Edit' class='cscm_edit_btn btn btn-smxl btn-primary' ><i class='bi bi-pencil-fill'></i></button> <button type='button' title='Delete' class='cscm_delete_btn btn btn-smxl btn-danger' ><i class='bi bi-x-circle-fill'></i></button> <button type='button' title='Toggle Status' class='cscm_toggle_btn btn btn-smxl btn-info' ><i class='bi bi-toggle-on'></i></button>"}
		];
		
		return tableColumnDefs['cscm_checks_tbl'];		

	}
	
	function cscm_get_default_checks_column_def(table_name)	
	{
		
///not used anywhere
		var tableColumnDefs = {};
		tableColumnDefs['cscm_default_checks_tbl']=[
        {readonly: true},
		{'type':'select','options':{},readonly: {'editRowBtn':true,'addRowBtn':false},required: true},
		{readonly: false,'width':'30%',hidden:{'editRowBtn':false,'addRowBtn':false}},
		{'type':'select','options':{ 'Y' : 'Y', 'N' : 'N' },hidden:{'editRowBtn':false,'addRowBtn':false}},
		{'type':'email'},
		{readonly: false,type:'hidden'},
		{readonly: false,type:'hidden',width:'5%'},
		{readonly: false,type:'hidden', "targets": -1, "data": null,"defaultContent": "<button type='button' title='Edit' class='cscm_edit_btn btn btn-smxl btn-primary' ><i class='bi bi-pencil-fill'></i></button> <button type='button' title='Delete' class='cscm_delete_btn btn btn-smxl btn-danger' ><i class='bi bi-x-circle-fill'></i></button> <button type='button' title='Toggle Status' class='cscm_toggle_btn btn btn-smxl btn-info' ><i class='bi bi-toggle-on'></i></button>"}
		];
		
		return tableColumnDefs['cscm_checks_tbl'];		

	}	
	
	function cscm_build_card_child(parent_id,child_id,child_header,child_details,child_row)
	{
		var uniq_id=parent_id+"_"+child_id;
		var card_template=`
			<div class="card">
				<h5 class="card-header" role="tab" id="child_alert_heading_${uniq_id}">
					<a  class="collapsed d-block" data-toggle="collapse" data-parent="#accordion" href="#child_alert_text_${uniq_id}" aria-expanded="true" aria-controls="child_alert_text_${uniq_id}" >
						<i class="fa fa-chevron-down pull-right"></i> ${child_header}
					</a>
				</h5>

				<div id="child_alert_text_${uniq_id}" class="collapse" role="tabpanel" aria-labelledby="child_alert_heading_${uniq_id}">
					<div class="card-body">
					${child_details}
					</div>
				</div>
			</div>					
`;		

		return card_template;
	}
	
	function cscm_build_card_accordion(parent_id,child_list)
	{
		var card_html="";
		var v_summary_key="alert_key";
		
		var prev_summary_key=child_list[0][v_summary_key];
		var summary_para="";
		for (var i=0;i<child_list.length;i++)
		{
			if (child_list[i][v_summary_key]==prev_summary_key)
			{
				summary_para+=child_list[i]["alert_body"]+"<br><hr>";
			}
			else
			{
				//cscm_build_card_child(parent_id,child_id,child_header,child_details,child_row)
				card_html+=cscm_build_card_child(parent_id,i,prev_summary_key,summary_para,null);
				prev_summary_key=child_list[i][v_summary_key];
				summary_para=child_list[i]["alert_body"]+"<br><hr>";
			}
			//card_html+=cscm_build_card_child(parent_id,i,child_list[i].diff_status+" Tag:"+child_list[i].test_name,child_list[i].alert_text,child_list[i]);
		}
		//cscm_build_card_child(parent_id,child_id,child_header,child_details,child_row)
		card_html+=cscm_build_card_child(parent_id,i,prev_summary_key,summary_para,null);
		var card_accordion=`
		<div id="child_accordion${parent_id}" role="tablist" aria-multiselectable="true">
			${card_html}
		</div>
`;
		return card_accordion;
	}
	
	function cscm_get_url_key(p_url_record,p_type)
	{
		if (p_type=="id")
		{
			var url_key=`${p_url_record.batch_id}`+"_" + `${p_url_record.list_url_id}`;
			return url_key;
		}
		else
		{
			var url_key=`${p_url_record.batch_id}`+" : " + `${p_url_record.target_url}`;
			return url_key;
			
		}
	}
	
	function cscm_build_full_card_stack(alert_list)
	{
		var url=alert_list[0].target_url;
		var url_id=alert_list[0].list_url_id; //297
		var url_key=alert_list[0].url_key; //2021-12-01_297
		var url_key_display=alert_list[0].url_key_disp; ////2021-12-01_crawlspider
		
		var child_list=[];
		var top_child_list=[];
		var top_accordion_html="";
		
		var curr_url_key="";
		var curr_url_key_disp="";
		for (var i=0;i<alert_list.length;i++)
		{
			curr_url_key=alert_list[i].url_key; //2021-12-01_297
			curr_url_key_disp=alert_list[i].url_key_disp; ////2021-12-01_crawlspider
			
			if (curr_url_key==url_key)
			{
				child_list.push(alert_list[i]);
			}
			else
			{
				//build the accordion for the previous child list
				                     //cscm_build_card_accordion(parent_id,child_list)
				var url_details_html=cscm_build_card_accordion(url_key,child_list);
				
			                  	//cscm_build_card_child(parent_id,child_id,child_header,child_details,child_row)
				var top_card_html=cscm_build_card_child("top",url_key,url_key_display,url_details_html,null);
				top_accordion_html+=top_card_html;
				
				//set new url
				url_key=curr_url_key;
				url_key_display=curr_url_key_disp;
				
				child_list=[];
				child_list.push(alert_list[i]);
			}
		}
		//build the accordion for the previous child list
		//cscm_build_card_accordion(parent_id,child_list)
		var url_details_html=cscm_build_card_accordion(url_key,child_list);
		//cscm_build_card_child(parent_id,child_id,child_header,child_details,child_row)
		var top_card_html=cscm_build_card_child("top",url_key,url_key_display,url_details_html,null);
		top_accordion_html+=top_card_html;
		
	
		
		var top_accordion=`
		<div id="top_accordion" role="tablist" aria-multiselectable="true">
			${top_accordion_html}
		</div>
`;		
		return top_accordion;
		
	}
	
})( jQuery );

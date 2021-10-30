/* This file is loaded when Organizr is loaded */
// Load once Organizr loads
$('body').arrive('#activeInfo', {onceOnly: true}, function() {
	ipRegistrationPluginLaunch();
});
function ipRegistrationPluginGenerateAPIKey() {
	document.getElementsByName("IPREGISTRATION-ApiToken")[0].value = createRandomString(20);
}
function ipRegistrationPluginLaunch(){
	organizrAPI2('GET','api/v2/plugins/ipregistration/launch').success(function(data) {
		try {
			var menuList = `<li><a href="javascript:void(0)" onclick="toggleIPRegistrationPlugin();"><i class="fa fa-list fa-fw"></i> <span lang="en">Registered IPs</span></a></li>`;
			$('.append-menu').after(menuList);
		}catch(e) {
			organizrCatchError(e,data);
		}
	}).fail(function(xhr) {
		OrganizrApiError(xhr);
	});

}
function toggleIPRegistrationPlugin(){
	let div = `
	<div class="panel bg-org panel-info" id="ipQuery-area">
		<div class="panel-heading">
			<span lang="en">Query IP Addresses</span>
		</div>
		<div class="panel-body">
			
			<div id="queryIPTable">
				<div class="white-box m-b-0">
					<h2 class="text-center loadingQueryIP" lang="en"><i class="fa fa-spin fa-spinner"></i></h2>
					<div class="row">
						<div class="col-lg-12">
							<select class="form-control" name="ipUsers" id="ipUsers" style="display:none">
								<option value="">Choose a User</option>
							</select><br>
						</div>
					</div>
					<div class="table-responsive queryIPTableList hidden" id="queryIPTableList">
						<label>Search:&nbsp;				
							<input type="text" id="ipSearch" placeholder="" />
						</label>
						<table class="table color-bordered-table danger-bordered-table text-left">
							<thead>
								<tr>
									<th>Date/Time</th>
									<th>Type</th>
									<th>IP Address</th>
									<th>Username</th>
									<th>DELETE</th>
								</tr>
							</thead>
							<tbody id="queryIP"></tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	`;
	swal({
		content: createElementFromHTML(div),
		button: false,
		className: 'orgAlertTransparent',
	});
	ipRegistrationPluginLoadIPs();
}

function ipRegistrationPluginLoadIPs(){
	organizrAPI2('GET','api/v2/plugins/ipregistration/query').success(function(data) {
		$('.loadingQueryIP').remove();
		try {
			const thtml = $("#queryIP");
			if (data.response.data) {
				if($('.queryIPTableList').hasClass('hidden')){
					$('.queryIPTableList').removeClass('hidden');
				}
				$.each(data.response.data, function(_, ip) {
					let ipItem = `
					<tr class="ipUsers ${ip.username} ipItem-${ip.id}">
						<td>${ip.datetime}</td>
						<td>${ip.type}</td>
						<td>${ip.ip}</td>
						<td>${ip.username}</td>
						<td class="deleteButton"><button type="button" class="btn btn-danger btn-outline btn-circle btn-lg m-r-5" onclick="deleteIP('${ip.id}');"><i class="ti-trash"></i></button></td>
					</tr>
				`;
					thtml.append(ipItem);
				});
			}
			thtml.append('<script>ipRegistrationPluginOnSearch();</script>');
		}catch(e) {
			organizrCatchError(e,data);
		}
	}).fail(function(xhr) {
		$('.loadingQueryIP').remove();
		OrganizrApiError(xhr);
	});
}

function deleteIP(id){
	ajaxloader(".content-wrap","in");
	organizrAPI2('DELETE','api/v2/plugins/ipregistration/ip/' + id).success(function(data) {
		var response = data.response;
		$('.ipItem-'+id).remove();
		//$.magnificPopup.close();
		ajaxloader();
		message('IP Registration Plugin',' IP Address Deleted Successfully',activeInfo.settings.notifications.position,'#FFF','success','5000');
	}).fail(function(xhr) {
		console.error("Organizr Function: API Connection Failed");
		ajaxloader();
		message('IP Registration Plugin','Failed to delete IP Address',activeInfo.settings.notifications.position,'#FFF','error','5000');
	});

}

function ipRegistrationPluginOnSearch() {
	$("#ipSearch").keyup(function(){
		var searchText = $(this).val().toLowerCase();
		$.each($("#queryIPTableList table tbody tr"), function() {
			if($(this).text().toLowerCase().indexOf(searchText) === -1)
				$(this).hide();
			else
			$(this).show();                
		});
	});
}
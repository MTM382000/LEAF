/************************
    Group Selector (Org Chart)
    Author: Michael Gao (Michael.Gao@va.gov)
    Date: March 2, 2012
*/

function groupSelector(containerID) {
	this.basePath = '';
	this.apiPath = './api/?a=';
	this.selection = '';

	this.containerID = containerID;
	this.prefixID = 'grpSel' + Math.floor(Math.random()*1000) + '_';
	this.timer = 0;
	this.q = '';
	this.isBusy = 1;
	this.backgroundImage = 'images/indicator.gif';
	this.intervalID = null;
	this.tag = '';
	this.selectHandler = null;
	this.resultHandler = null;
	this.selectLink = null;
	this.selectionData = new Object();
	this.inputID = '#' + this.prefixID+ 'input';
	this.optionNoLimit = 0;
	this.currRequest = null;
	this.jsonResponse = null;

	this.numResults = 0;
};

groupSelector.prototype.initialize = function() {
    var t = this;
	$('#' + this.containerID).html('<div id="'+this.prefixID+'border" class="groupSelectorBorder">\
			<div style="float: left"><img id="'+this.prefixID+'icon" src="'+ this.basePath +'../libs/dynicons/?img=search.svg&w=16" class="groupSelectorIcon" alt="search" />\
			<img id="'+this.prefixID+'iconBusy" src="'+ this.basePath +'images/indicator.gif" style="display: none" class="groupSelectorIcon" alt="search" /></div>\
			<input id="'+this.prefixID+'input" type="search" class="groupSelectorInput" aria-label="search"></input></div>\
			<div id="'+this.prefixID+'result"></div>');

	$(this.inputID).on('keydown', function(e) {
		t.showBusy();
		t.timer = 0;
		if($(t.inputID).val() == '') {
			t.q = '';
		}
		if(e.keyCode == 13) { // enter key
			t.q = '';
			t.search();
		}
	});

	this.showNotBusy();
    this.intervalID = setInterval(function(){t.search();}, 200);
};

groupSelector.prototype.showNotBusy = function() {
	if(this.isBusy == 1) {
		$('#' + this.prefixID + 'icon').css('display', 'inline');
		$('#' + this.prefixID + 'iconBusy').css('display', 'none');
		this.isBusy = 0;
	}
};

groupSelector.prototype.showBusy = function() {
	$('#' + this.prefixID + 'icon').css('display', 'none');
	$('#' + this.prefixID + 'iconBusy').css('display', 'inline');
	this.isBusy = 1;
};

groupSelector.prototype.select = function(id) {
	this.selection = id;

	nodes = $('#'+ this.containerID +' .groupSelected');
	for(var i in nodes) {
		if(nodes[i].id != undefined) {
			$('#' + nodes[i].id).removeClass('groupSelected');
			$('#' + nodes[i].id).addClass('groupSelector');
		}
	}

	$('#' + this.prefixID + 'grp' + id).addClass('groupSelected');
	$('#' + this.prefixID + 'grp' + id).removeClass('groupSelector');

	if(this.selectHandler != null) {
		this.selectHandler();
	}
};

groupSelector.prototype.searchTag = function(tag) {
	this.tag = tag;
};

groupSelector.prototype.setSelectHandler = function(func) {
	this.selectHandler = func;
};

groupSelector.prototype.setResultHandler = function(func) {
	this.resultHandler = func;
};

groupSelector.prototype.setSelectLink = function(link) {
	this.selectLink = link;
};

groupSelector.prototype.clearSearch = function() {
	$('#' + this.prefixID+ 'input').val('');
};

groupSelector.prototype.forceSearch = function(query) {
	$('#' + this.prefixID + 'input').val(query.replace(/<[^>]*>/g, ''));
};

groupSelector.prototype.hideInput = function() {
	$('#' + this.prefixID + 'border').css('display', 'none');
};

groupSelector.prototype.hideResults = function() {
	$('#' + this.prefixID + 'result').css('display', 'none');
};

groupSelector.prototype.showResults = function() {
	$('#' + this.prefixID + 'result').css('display', 'inline');
};

groupSelector.prototype.enableNoLimit = function() {
	this.optionNoLimit = 1;
};

groupSelector.prototype.configInputID = function(inputID) {
	this.inputID = inputID;
};

groupSelector.prototype.search = function() {
	if($('#' + this.prefixID + 'input').val() == undefined
		|| $('#' + this.prefixID + 'input') == null) {
		clearInterval(this.intervalID);
		return false;
	}
	this.timer += (this.timer > 5000) ? 0 : 200;

	if(this.timer > 300) {
    	var skip = 0;
	    var txt = $('#' + this.prefixID + 'input').val().replace(/<[^>]*>/g, '');
	    if(txt == undefined) {
	    	clearInterval(this.intervalID);
	    	return false;
	    }
    	if(this.q.length != 0 && this.q.length < txt.length && this.numResults == 0) {
    		skip = 1;
    	}

	    if(txt != "" && txt != this.q && skip == 0) {
	    	this.q = txt;

	    	if(this.currRequest != null) {
	    		this.currRequest.abort();
	    	}

	    	var t = this;
	    	this.currRequest = $.ajax({
	            url: this.apiPath + "group/search",
	            dataType: 'json',
		    	data: {q: this.q,
		    		      tag: this.tag,
		    		      noLimit: this.optionNoLimit},
	            success: function(response) {
	            	t.currRequest = null;
	            	t.selection = '';
	            	t.numResults = 0;
	            	t.jsonResponse = response;

	            	$('#' + t.prefixID + 'result').html('');
	            	var buffer = '<table class="groupSelectorTable"><tr><th>Group Title</th></tr><tbody id="' + t.prefixID + 'result_table"></tbody></table>';
	            	$('#' + t.prefixID + 'result').html(buffer + $('#' + t.prefixID + 'result').html());

	            	if(response.length == 0) {
	            		$('#' + t.prefixID + 'result_table').append('<tr id="' + t.prefixID + 'emp0"><td style="font-size: 120%; background-color: white; text-align: center">No results for &quot;<span style="color: red">'+ txt +'</span>&quot;</td></tr>');
	            	}

					t.selectionData = new Object();
	                $.each(response, function(key, item) {
						t.selectionData[item.groupID] = item;
		                	

	                	linkText = item.groupTitle;
	                	if(t.selectLink != null) {
	                		linkText = '<a href="'+ t.selectLink +'&groupID='+ item.groupID +'">' + linkText + '</a>';
	                	}

	                	$('#' + t.prefixID + 'result_table').append('<tr id="'+ t.prefixID + 'grp' + item.groupID +'"><td class="groupSelectorTitle" title="' + item.groupID + '">' + linkText + '</td></tr>');
	                	$('#' + t.prefixID + 'grp' + item.groupID).addClass('groupSelector');

	                	$('#' + t.prefixID + 'grp' + item.groupID).on('click', function() {
	                		t.select(item.groupID);
	                	});
	                	t.numResults++;
	                });

	            	if(t.numResults == 1) {
	            		t.selection = response[0].groupID;
	            	}

	            	if(t.resultHandler != null) {
	            		t.resultHandler();
	            	}

	                t.showNotBusy();
	            },
	            cache: false
	        });
	    }
	    else if(txt == "") {
	    	this.q = txt;
	    	$('#' + this.prefixID + 'result').html('');
	    	this.numResults = 0;
	    	this.selection = '';
	    	if(this.resultHandler != null) {
        		this.resultHandler();
        	}
	        this.showNotBusy();
	    }
	    else {
	    	this.showNotBusy();
	    }
	}
};

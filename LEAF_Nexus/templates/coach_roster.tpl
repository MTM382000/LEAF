<div id="rosterHeader">
    <h1>Find a LEAF Coach</h1>
</div>

<div id="searchBar">
    <input id="searchRosterInput" type="text" placeholder="Search by Name, Process, or Location" />
    <a id="searchBtnAnchor" href="#">
        <img id="searchRosterBtn" 
            class="searchIcon" 
            src="../libs/dynicons/?img=search.svg&w=25" 
            alt="search icon" />
    </a>
</div>

<div id="coaches"></div>

<script type="text/javascript">

function buildCoachProfile(coach) {
    // slightly faster than $("<div>")...
    var coachDiv = $(document.createElement('div')).addClass('coach');

    var topDiv = $(document.createElement('div')).addClass('top').appendTo(coachDiv);

    var imgDiv = 
        $(document.createElement('img'))
            .addClass('profileImage')
            .attr('src', coach.pictureSrc)
            .attr('alt', 'profile image')
            .appendTo(topDiv);

    var infoDiv = $(document.createElement('div')).addClass('info').appendTo(topDiv);
    var nameDiv = 
        $(document.createElement('div'))
            .addClass('name')
            .html(coach.name)
            .appendTo(infoDiv);

    var phoneDiv = $(document.createElement('div')).addClass('phone').html(coach.phone).appendTo(infoDiv);
    if (coach.pulse !== undefined && coach.pulse.length > 0) {
        var pulseDiv = $(document.createElement('div'))
            .addClass('pulseBioLink').appendTo(infoDiv);
        var anchorDiv = $(document.createElement('a'))
            .attr('href', coach.pulse).html('Pulse Bio Page').appendTo(pulseDiv);
    }

    var locationNameDiv = 
        $(document.createElement('div')).addClass('locationName').html(coach.facility).appendTo(infoDiv);
    var geoLocationDiv = 
        $(document.createElement('div')).addClass('geoLocation').html(coach.location).appendTo(infoDiv);
    
    var specialtiesDiv = $(document.createElement('div')).addClass('specialties').appendTo(coachDiv);
    $(document.createElement('ul')).html(coach.process).appendTo(specialtiesDiv);

    return coachDiv;
}

function clearCurrentRoster() {
    $('#coaches').empty();
}

function populateRoster(coaches) {
    coaches.forEach(function(coach) {
        $('#coaches').append(buildCoachProfile(coach));
    });
}

function searchForCoaches() {
    var coachQuery = new CoachQuery($('#searchRosterInput').val());

    portalAPI.Forms.query(
        coachQuery.buildQuery(),
        function (results) {
            clearCurrentRoster();
            populateRoster(coachQuery.parseResults(results));
        },
        function (err) {
            console.log(err);
        }
    );
}

this.portalAPI = new LEAFRequestPortalAPI();

$(function() {
    $('#searchBtnAnchor').click(function(e) {
        e.preventDefault();
        searchForCoaches();
    });

    $('#searchRosterInput').keypress(function(e) {
        // if keycode is 'Enter'
        if (e.which == 13) {
            searchForCoaches();
            return false;
        }
    });

    searchForCoaches();
});

</script>
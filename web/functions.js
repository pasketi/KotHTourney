$("document").ready(function() {
    //$("#content").append("<p>jQuery is working.</p>");
    //alert("Text length: " +$(".css-champion-title").text().length);
    ReduceTitleWidth('.css-champion-title');
    })

function ReduceTitleWidth(id) {
    var name = $(id);
    if (name.text().length > 19) {
        name.css("font-size", 24);
        
    }
    
    if (name.text().length > 22) {
        name.css("font-size", 18);
    }
}

$(function() {
$('.css-button-leaderboards-toggle').click(function(){
		var tab_id = $(this).attr('data-tab');

		$('.css-button-leaderboards-toggle').removeClass('current');
		$('.tab-content').removeClass('current');

		$(this).addClass('current');
		$("#"+tab_id).addClass('current');
	})
});

/*$(function() {
 $(".css-button-leaderboards-toggle").click(function() {
    $(this).toggleClass('active');
    $('.css-list-streaks, #header-streaks').toggle();
    $('.css-list-contenders, #header-contenders').toggle();
    });
 });*/
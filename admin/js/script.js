$(document).ready(function(){

var user_href;
var user_href_splitted;
var user_id;

var image_src;
var image_href_splitted;
var image_name;
var photo_id;



	$(".modal_thumbnails").click(function(){

		$("#set_user_image").prop('disabled',false);

		user_href=$("#user-id").prop("href");
		//dijelimo string na dva dijela i dobijamo niz 
		user_href_splitted=user_href.split('=');
		//uzimamo dio niza koji sadrzi id tj drugi dio
		user_id=user_href_splitted[user_href_splitted.length-1];


		//klikom na element izvalacimo potrebne podatke iz njega
		image_src=$(this).prop('src');
		image_href_splitted=image_src.split('/');
		image_name=image_href_splitted[image_href_splitted.length-1];

		photo_id=$(this).attr("data");

		$.ajax({
			url:"includes/ajax_code.php",
			data:{photo_id:photo_id},
			type:"POST",
			success:function(data){
				if(!data.error){
					$("#modal_sidebar").html(data);
				}
			}

		});

	});

	$("#set_user_image").click(function(){

	$.ajax({
		url:"includes/ajax_code.php",
		data:{image_name:image_name,user_id:user_id},
		type:"POST",
		success:function(data){
			if(!data.error){
				$(".user_image_box a img").prop('src', data);
				// location.reload(true);
			}
		}

	});


	});

/*********EDIT PHOTO SIDEBAR*********/
/*toggle save*/
$(".info-box-header").click(function(){
	$('.inside').slideToggle('slow');
	$('#toggle').toggleClass('fa fa-chevron-down , fa fa-chevron-up');
});
/*kraj toggle save*/

// delete event
$(".delete_link").click(function(){
	//javascript naredba
	return confirm("Are you sure you want delete this item");
});


// kraj delete


tinymce.init({ selector:'textarea' });


});
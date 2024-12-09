$(document).ready(function(){
    $('.card').hover(
        function(){
            $(this).addClass('shadow-lg').css('cursor', 'pointer'); 
        }, 
        function(){
            $(this).removeClass('shadow-lg');
        }
    );
});
const shareButton = document.querySelectorAll("button.shareButton")

shareButton[0].addEventListener("click", (e) => {
    for( let i=0; i < shareButton.length; i++ ) {
       shareButton[i].classList.toggle("open")
       shareButton[0].classList.remove("sent")
    }
})

for( let i=1; i < shareButton.length; i++ ) {
   
   shareButton[i].addEventListener("click", (e) => {
      
   for( let i=0; i < shareButton.length; i++ ) {
      shareButton[i].classList.toggle("open")
   }
   shareButton[0].classList.toggle("sent")
   })
}
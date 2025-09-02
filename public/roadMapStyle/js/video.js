let videoList = document.querySelectorAll('.video-list-container .list-item');

videoList.forEach(vid =>{
   vid.onclick = () =>{
      videoList.forEach(remove =>{remove.classList.remove('active')});
      vid.classList.add('active');
      let src = vid.querySelector('.list-video').src;
      document.querySelector('.main-video-container .vjs-tech').src = src;
      document.querySelector('.main-video-container .vjs-tech').play();
   };
});
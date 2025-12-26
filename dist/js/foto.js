(function () {
  var streaming = false,
    video = document.querySelector("#video"),
    cover = document.querySelector("#cover"),
    canvas = document.querySelector("#canvas"),
    photo = document.querySelector("#photo"),
    startbutton = document.querySelector("#startbutton"),
    width = 200,
    height = 0;

  navigator.getUserMedia =
    navigator.getUserMedia ||
    navigator.webkitGetUserMedia ||
    navigator.mozGetUserMedia;

  if (navigator.getUserMedia) {
    navigator.getUserMedia(
      { audio: false, video: { width: 130, height: 130 } },
      function (stream) {
        var video = document.querySelector("video");
        video.srcObject = stream;
        video.onloadedmetadata = function (e) {
          video.play();
        };
      },
      function (err) {
        console.log("The following error occurred: " + err.name);
      }
    );
  } else {
    console.log("getUserMedia not supported");
  }

  video.addEventListener(
    "canplay",
    function (ev) {
      if (!streaming) {
        height = video.videoHeight / (video.videoWidth / width);
        video.setAttribute("width", width);
        video.setAttribute("height", height);
        canvas.setAttribute("width", width);
        canvas.setAttribute("height", height);
        streaming = true;
      }
    },
    false
  );

  function takepicture() {
    canvas.width = width;
    canvas.height = height;
    canvas.getContext("2d").drawImage(video, 0, 0, width, height);
    var data = canvas.toDataURL("image/png");
    var aDownloadLink = document.createElement("a");
    var fecha = new Date();
    aDownloadLink.download = `${
      fecha.getDate() + "/" + (fecha.getMonth() + 1) + "/" + fecha.getFullYear()
    }.png`;
    aDownloadLink.href = data;
    aDownloadLink.click();

    // photo.setAttribute("src", data);
  }

  startbutton.addEventListener(
    "click",
    function (ev) {
      takepicture();
      ev.preventDefault();
    },
    false
  );
})();

<script type="text/javascript">
  const CiM = {
    myCropme: null,
    opt: {
      my_win_ratio: 1,
      my_final_size: { w: 534, h: 534 },
      container: { width: 0, height: 0 },
      viewport: {
        width: 0,
        height: 0,
        type: 'circle',
        border: { width: 2, enable: true, color: '#fff' }
      },
      zoom: { enable: true, mouseWheel: true, slider: true },
      rotation: { slider: true, enable: true },
      transformOrigin: 'viewport',
    },
    
    // Crops the image and triggers the callback
    crop_into_img: async function (img, callback) {
      try {
        const res = await CiM.myCropme.crop({
          width: CiM.opt.my_final_size.w,
        });
        img[0].src = res;
        CiM.myCropme.destroy();
        CiM.myCropme = null;
        if (callback) callback();
      } catch (error) {
        console.error('Error cropping image:', error);
      }
    },

    imgHolder: null,
    imgHolderCallback: null,
    
    // Reads image file and triggers the callback once loaded
    read_file_from_input: function (input, callback) {
      if (input.files && input.files[0]) {
        CiM.imgHolderCallback = callback;
        const reader = new FileReader();
        if (!CiM.imgHolder) {
          CiM.imgHolder = new Image();
          CiM.imgHolder.onload = function () {
            if (CiM.imgHolderCallback) {
              CiM.imgHolderCallback();
            }
          }
        }
        reader.onload = function (e) {
          CiM.imgHolder.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
      } else {
        console.warn('Failed to read file');
      }
    },

    // Generates a placeholder image using SVG
    getImagePlaceholder: function (width, height, text) {
      const svg = `
        <svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" viewBox="0 0 ${width} ${height}">
          <rect fill="#ddd" width="${width}" height="${height}"/>
          <text fill="rgba(0,0,0,0.5)" font-family="sans-serif" font-size="30" dy="10.5" font-weight="bold" x="50%" y="50%" text-anchor="middle">${text}</text>
        </svg>`;
      
      const encoded = encodeURIComponent(svg)
        .replace(/\(/g, '%28')
        .replace(/\)/g, '%29');

      return 'data:image/svg+xml;charset=UTF-8,' + encoded;
    },

    // Returns a default image placeholder
    get_image_placeholder: function (text) {
      return CiM.getImagePlaceholder(CiM.opt.my_final_size.w, CiM.opt.my_final_size.h, text);
    },

    // Uploads the image as Base64 encoded data
    uploadImage: function (img, callback) {
      const imgCanvas = document.createElement("canvas");
      const imgContext = imgCanvas.getContext("2d");

      imgCanvas.width = img.width;
      imgCanvas.height = img.height;

      imgContext.drawImage(img, 0, 0, img.width, img.height);

      const dataURL = imgCanvas.toDataURL();

      $.ajax({
        type: "POST",
        url: "./post.php", // Replace with your backend script URL
        data: { imgBase64: dataURL }
      }).done(function (resp) {
        if (callback) callback(resp);
        location.reload();
      }).fail(function (error) {
        console.error('Upload failed:', error);
      });
    },

    // Updates options for the cropping window based on new width
    update_options_for_width: function (w) {
      const o = CiM.opt;
      const vp_ratio = o.my_final_size.w / o.my_final_size.h;
      let h, new_vp_w, new_vp_h;

      w = Math.floor(w * 0.9);
      h = Math.floor(w / o.my_win_ratio);
      o.container.width = w;
      o.container.height = h;

      new_vp_h = 0.6 * h;
      new_vp_w = new_vp_h * vp_ratio;

      if (new_vp_w > 0.6 * w) {
        new_vp_w = 0.6 * w;
        new_vp_h = new_vp_w / vp_ratio;
      }

      o.viewport.width = Math.floor(new_vp_w);
      o.viewport.height = Math.floor(new_vp_h);
    },

    // Displays the cropping interface in the specified div
    show_cropme_in_div: function (cropme_div) {
      if (CiM.myCropme) CiM.myCropme.destroy();
      CiM.myCropme = new Cropme(cropme_div, CiM.opt);
      CiM.myCropme.bind({ url: CiM.imgHolder.src });
    }
  };

  // Initializes the page once it is fully loaded
  window.onload = function () {
    const croppedImg = $('#cropped-img');
    const savedImg = $('#saved-img');
    const btnSave = $('#imgModal-btnSave');
    const btnCrop = $('#imgModal-btnCrop');
    
    CiM.opt.my_final_size = { w: 534, h: 534 };
    CiM.opt.my_win_ratio = 1.5;
    savedImg[0].src = CiM.get_image_placeholder('?');

    // Event listeners for buttons
    btnCrop.on('click', function () {
      CiM.crop_into_img(croppedImg, function () {
        btnSave.show();
        btnCrop.hide();
      });
    });

    btnSave.on('click', function () {
      CiM.uploadImage(croppedImg[0], function (path_to_saved) {
        savedImg[0].src = path_to_saved;
        $('#imgModal-dialog').modal('hide');
      });
    });

    $('#btnGetImage').on('click', function () {
      $('#fileUpload').prop("value", "");
      $('#fileUpload').click();
    });

    $('#fileUpload').on('change', function () {
      CiM.read_file_from_input(this, function () {
        $('#imgModal-dialog').modal('show');
      });
    });

    // Show cropping interface once modal is shown
    $('#imgModal-dialog').on('shown.bs.modal', function () {
      const cropZone = $('#imgModal-cropme');
      CiM.update_options_for_width($('#imgModal-msg').width());
      btnSave.hide();
      btnCrop.show();
      croppedImg[0].src = '';
      CiM.show_cropme_in_div($('#imgModal-cropme')[0]);
    });

    // Update options on window resize
    $(window).resize(function () {
      CiM.update_options_for_width($('#imgModal-msg').width());
      CiM.show_cropme_in_div($('#imgModal-cropme')[0]);
    });
  };
</script>

<style>
  .cropme-container {
    max-width: 100%;
    position: relative;
  }

  #imgModal-cropme {
    direction: ltr;
    position: relative;
    width: 100%;
    height: auto;
    margin: 0 auto;
  }

  #cropped-img-wrp {
    width: 100%;
    height: auto;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  #cropped-img {
    max-width: 100%;
    max-height: 100%;
    border: 2px solid white;
  }

  .modal-body {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100%;
  }
</style>

<body>
  <main role="main" class="container-fluid">
    <div class="row">
      <div class="col text-center p-2">
        <div class="container mt-5">
          <div class="text-center">
            <p>Please add an image you would like to use for your proforma</p>
          </div>
        </div>

        <img class="mt-4" id="saved-img" style="height: 25%; width: 50%">
        <div class="mt-3">
          <button id="btnGetImage" class="btn btn-primary">Get Image</button>
          <input class="d-none" type="file" id="fileUpload" accept="image/*" />
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="imgModal-dialog" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
          <div id="imgModal-msg" class="modal-body mb-2" style="height: 70%">
            <div id="cropped-img-wrp"><img id="cropped-img"></div>
            <div id="imgModal-cropme-wrp" class="justify-content-center">
              <div id="imgModal-cropme" class=""></div>
            </div>
          </div>
          <div class="modal-footer justify-content-center">
            <button id="imgModal-btnSave" class="btn btn-primary">Save Image</button>
            <button id="imgModal-btnCrop" type="button" class="btn btn-info">Crop</button>
            <button id="imgModal-btnCancel" type="button" class="btn btn-info" data-dismiss="modal">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropme@latest/dist/cropme.min.css">
  <script src="https://cdn.jsdelivr.net/npm/cropme@latest/dist/cropme.min.js"></script>
</body>

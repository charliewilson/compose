//Croppie
document.querySelector("#addImageButton").addEventListener("click", function(event) {
    // Open the new image modal
    $('#imageModal').modal('show');
});

let $uploadCrop;

function readFile(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();

        reader.onload = function (e) {
            $('#upload-demo').addClass('ready');
            $uploadCrop.croppie('bind', {
                url: e.target.result
            }).then(function(){
                console.log('jQuery bind complete');
            });

        }

        reader.readAsDataURL(input.files[0]);
    }
}

$uploadCrop = $('#upload-demo').croppie({
    viewport: {
        width: 300,
        height: 200
    },
    boundary: {
        width: 300,
        height: 200
    },
    enableExif: true
});

let saveImageButton = document.querySelector("a.btn-saveimage");

$('#imageUpload').on('change', function () {
    readFile(this);
    saveImageButton.removeAttribute('disabled');
    saveImageButton.classList.remove('disabled');
});

$('.btn-saveimage').on('click', function (ev) {
    $uploadCrop.croppie('result', {
        type: 'base64',
        size: {
            'width': 770
        }
    }).then(function (resp) {
        document.querySelector("#image_encoded").value = resp;
        document.querySelector("#thumbnail_placeholder").src = resp;
        document.querySelector("#addImageButton").innerHTML = "Edit Image";
        $("#imageModal").modal('hide');
    });
});
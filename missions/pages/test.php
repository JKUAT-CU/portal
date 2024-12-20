<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CropMe Integration</title>
    <script src="https://cdn.jsdelivr.net/npm/cropme"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropme/dist/cropme.css">
</head>
<body>
    <div id="crop-container" style="width: 300px; height: 300px; margin: auto;"></div>
    <input type="file" id="file-input" />
    <button id="crop-btn">Crop & Upload</button>

    <script>
        // Initialize CropMe
        const cropper = new Cropme('#crop-container', {
            container: { width: 300, height: 300 },
            viewport: { width: 200, height: 200, type: 'circle' }, // Circle viewport for profile pictures
            zoom: { min: 1, max: 3 },
        });

        // Handle file input
        document.getElementById('file-input').addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = () => cropper.bind({ url: reader.result });
                reader.readAsDataURL(file);
            }
        });

        // Handle cropping and uploading
        document.getElementById('crop-btn').addEventListener('click', async () => {
            try {
                const croppedImageBase64 = await cropper.result({ type: 'base64', size: 'viewport' });
                const response = await fetch('/path-to-backend-script.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ imgBase64: croppedImageBase64 }),
                });

                const result = await response.text();
                console.log(result); // Process backend response
                alert(result);
            } catch (error) {
                console.error('Error cropping or uploading image:', error);
            }
        });
    </script>
</body>
</html>

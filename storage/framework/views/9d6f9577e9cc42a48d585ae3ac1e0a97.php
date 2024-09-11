<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate UI with AI</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
            color: #333;
            margin: 0;
            padding: 0;
            text-align: center;
        }
        #header {
            background-color: #007bff;
            color: #fff;
            padding: 20px 0;
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        #header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        #upload-container {
            margin: 0 auto;
            padding: 30px;
            max-width: 600px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        #upload-container h2 {
            margin-top: 0;
            font-size: 22px;
            color: #007bff;
            margin-bottom: 20px;
        }
        #upload-container input[type="file"] {
            display: block;
            margin: 20px auto;
            padding: 10px;
            border: 2px solid #007bff;
            border-radius: 8px;
            background-color: #fff;
            cursor: pointer;
            outline: none;
            font-size: 16px;
        }
        #image-preview {
            display: none;
            max-width: 100%;
            margin: 20px auto;
            border: 2px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .button-container {
            display: flex;
            justify-content: center;
            gap: 15px; /* Space between buttons */
            margin-top: 20px;
        }
        .button-container button {
            padding: 12px 25px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 8px;
            color: #fff;
            background-color: #007bff;
            transition: background-color 0.3s, transform 0.2s;
            outline: none;
        }
        .button-container button:hover {
            background-color: #0056b3;
        }
        .button-container button:active {
            transform: scale(0.98);
        }
        #loading-spinner {
            display: none;
            margin-top: 20px;
            border: 4px solid #f3f3f3; /* Light grey */
            border-top: 4px solid #007bff; /* Blue */
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        #result-container {
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            text-align: center;
        }
        #result-container h2 {
            margin-top: 0;
            color: #007bff;
            font-size: 22px;
        }
        #result-container div {
            margin-bottom: 15px;
            font-size: 18px;
            color: #333;
        }
    </style>
</head>
<body>
    <div id="header">
        <h1>Rate UI with AI</h1>
    </div>

    <div id="upload-container">
        <h2>Upload an Image</h2>
        <input type="file" id="upload-input" accept="image/*" />
        <img id="image-preview" src="" alt="Image Preview" />
        <div class="button-container">
            <button id="upload-button" style="display:none;">Upload Image</button>
            <button id="process-button" style="display:none;">Process Image</button>
        </div>
        <div id="loading-spinner"></div>
    </div>

    <div id="result-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@latest/dist/tf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@teachablemachine/image@latest/dist/teachablemachine-image.min.js"></script>
    <script type="text/javascript">
        const URL = "https://teachablemachine.withgoogle.com/models/DqxhBVL0Z/";
        let model, maxPredictions;
        let imageCanvas = document.createElement("canvas");
        let uploadedImage = null;

        async function init() {
            try {
                const modelURL = URL + "model.json";
                const metadataURL = URL + "metadata.json";
                model = await tmImage.load(modelURL, metadataURL);
                maxPredictions = model.getTotalClasses();
                console.log("Model loaded successfully.");
            } catch (error) {
                console.error("Error loading model:", error);
            }
        }

        init();

        document.getElementById("upload-input").addEventListener("change", (event) => {
            const file = event.target.files[0];
            if (file) {
                const img = document.getElementById("image-preview");
                const reader = new FileReader();

                reader.onload = function(e) {
                    img.src = e.target.result;
                    img.style.display = "block";
                    uploadedImage = img;
                    document.getElementById("upload-button").style.display = "block";
                };

                reader.onerror = function() {
                    console.error("Error loading image.");
                };

                reader.readAsDataURL(file);
            } else {
                document.getElementById("upload-button").style.display = "none";
            }
        });

        document.getElementById("upload-button").addEventListener("click", () => {
            if (uploadedImage) {
                document.getElementById("process-button").style.display = "block";
            } else {
                alert("Please select an image first.");
            }
        });

        document.getElementById("process-button").addEventListener("click", async () => {
            if (uploadedImage) {
                document.getElementById("loading-spinner").style.display = "inline-block";
                try {
                    imageCanvas.width = uploadedImage.naturalWidth;
                    imageCanvas.height = uploadedImage.naturalHeight;
                    const context = imageCanvas.getContext("2d");
                    context.drawImage(uploadedImage, 0, 0, imageCanvas.width, imageCanvas.height);

                    const prediction = await model.predict(imageCanvas);
                    displayResults(prediction);
                } catch (error) {
                    console.error("Error during prediction:", error);
                } finally {
                    document.getElementById("loading-spinner").style.display = "none";
                }
            } else {
                alert("Please upload an image first.");
            }
        });

        function displayResults(predictions) {
            const resultContainer = document.getElementById("result-container");
            resultContainer.innerHTML = '';
            if (predictions && predictions.length > 0) {
                resultContainer.innerHTML = '<h2>Prediction Results:</h2>';
                predictions.forEach((prediction) => {
                    const div = document.createElement("div");
                    div.textContent = `${prediction.className}: ${prediction.probability.toFixed(2)}`;
                    resultContainer.appendChild(div);
                });
            } else {
                resultContainer.innerHTML = "No predictions available.";
            }
        }
    </script>
</body>
</html>
<?php /**PATH C:\Users\asus\project-1\resources\views/welcome.blade.php ENDPATH**/ ?>
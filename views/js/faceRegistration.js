/**
 * @param {string} lastName - The user's last name for face registration.
 * @param {HTMLVideoElement} videoElement - The video element to use.
 * @param {Object} options  
 */
async function faceRegistration(lastName, videoElement, options = {}) {
    const modelsPath = options.modelsPath || "models";
    const imagesPath = options.imagesPath || "labels";

    // Validate last name
    if (!lastName) {
        alert("Last name is required.");
        return;
    }

    // Show loading indicator
    const loadingDiv = document.createElement("div");
    loadingDiv.id = "face-loading-indicator";
    loadingDiv.style = `
        position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
        background: rgba(0,0,0,0.5); display: flex; align-items: center;
        justify-content: center; z-index: 9999; color: #fff; font-size: 2rem;
    `;
    loadingDiv.innerText = "Loading face models...";
    document.body.appendChild(loadingDiv);

    // Load face-api models
    await Promise.all([
        faceapi.nets.ssdMobilenetv1.loadFromUri(modelsPath),
        faceapi.nets.faceRecognitionNet.loadFromUri(modelsPath),
        faceapi.nets.faceLandmark68Net.loadFromUri(modelsPath),
    ]);

    loadingDiv.innerText = "Starting camera...";
    // Start webcam
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        videoElement.srcObject = stream;
        await new Promise(resolve => {
            videoElement.onloadedmetadata = () => {
                videoElement.play();
                resolve();
            };
        });
    } catch (err) {
        alert("Camera error: " + err);
        loadingDiv.remove();
        return;
    }

    // Remove loading overlay before showing camera
    loadingDiv.remove();

    // Create faceapi overlay canvas
    const canvas = faceapi.createCanvasFromMedia(videoElement);
    document.body.appendChild(canvas);

    // Match canvas size to video
    const displaySize = { width: videoElement.width, height: videoElement.height };
    faceapi.matchDimensions(canvas, displaySize);

    // Draw bounding box in real time
    let drawBox = true;
    async function drawFaceBoxLoop() {
        if (!drawBox) return;
        const detections = await faceapi.detectAllFaces(videoElement).withFaceLandmarks();
        const resizedDetections = faceapi.resizeResults(detections, displaySize);
        canvas.getContext("2d").clearRect(0, 0, canvas.width, canvas.height);
        faceapi.draw.drawDetections(canvas, resizedDetections);
        faceapi.draw.drawFaceLandmarks(canvas, resizedDetections);
        requestAnimationFrame(drawFaceBoxLoop);
    }
    drawFaceBoxLoop();

    // Show instruction overlay
    const instructionDiv = document.createElement("div");
    instructionDiv.id = "face-instruction-indicator";
    instructionDiv.style = `
        position: fixed; bottom: 2rem; left: 50%; transform: translateX(-50%);
        background: rgba(0,0,0,0.7); color: #fff; padding: 1rem 2rem;
        border-radius: 1rem; font-size: 1.2rem; z-index: 10001;
    `;
    instructionDiv.innerText = "Please position your face in the camera.";
    document.body.appendChild(instructionDiv);
    await new Promise(resolve => setTimeout(resolve, 1000));

    // Helper to capture and download image
    async function captureAndDownload(index) {
        const proceed = confirm(`Look at the camera and click OK to take photo ${index}, or Cancel to retake.`);
        if (!proceed) {
            alert("Retake the photo when ready.");
            return false;
        }

        // Draw current frame to canvas
        const captureCanvas = document.createElement("canvas");
        captureCanvas.width = videoElement.videoWidth;
        captureCanvas.height = videoElement.videoHeight;
        const ctx = captureCanvas.getContext("2d");
        ctx.drawImage(videoElement, 0, 0, captureCanvas.width, captureCanvas.height);

        // Detect face and only save if face is found
        const detection = await faceapi.detectSingleFace(captureCanvas).withFaceLandmarks()
            .withFaceDescriptor();
        if (!detection) {
            alert("No face detected. Please try again.");
            return false;
        }



        // Convert to JPEG
        const dataUrl = captureCanvas.toDataURL("image/jpeg");
        // Trigger download
        const a = document.createElement("a");
        a.href = dataUrl;
        a.download = `${imagesPath}/${lastName}/${index}.jpg`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        return true;
    }

    // Take 5 pictures
    for (let i = 1; i <= 5; i++) {
        instructionDiv.innerText = `Get ready for photo ${i}/5. Click OK to capture.`;
        await new Promise(resolve => setTimeout(resolve, 500));
        alert(`Look at the camera and click OK to take photo ${i}`);
        let success = false;
        while (!success) {
            success = await captureAndDownload(i);
            if (!success) alert("Face not detected. Try again.");
        }
    }

    drawBox = false; // Stop drawing boxes
    if (canvas) canvas.remove();
    if (instructionDiv) instructionDiv.remove();

    // Show completion message
    const doneDiv = document.createElement("div");
    doneDiv.id = "face-done-indicator";
    doneDiv.style = `
        position: fixed; bottom: 2rem; left: 50%; transform: translateX(-50%);
        background: #4caf50; color: #fff; padding: 1rem 2rem;
        border-radius: 1rem; font-size: 1.2rem; z-index: 10001;
    `;
    doneDiv.innerText = "Face registration complete! Check your downloads.";
    document.body.appendChild(doneDiv);
    setTimeout(() => doneDiv.remove(), 2000);
}
// faceRegistration("Smith", document.getElementById("videoElement"));
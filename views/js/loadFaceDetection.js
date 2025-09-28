
/**
 * Registers face detection and recognition on a video element.
 * @param {HTMLVideoElement} videoElement - The video element to use.
 * @param {Array} labels - Array of label names for face registration.
 * @param {Object} options - Optional settings (modelsPath, imagesPath, onDetect callback).
 */

async function loadFaceDetection(videoElement, labels, options = {}) {

    const modelsPath = options.modelsPath || "models";
    const imagesPath = options.imagesPath || "labels";
    const onDetect = options.onDetect || (() => { })


    // Create and show loading indicator
    const loadingDiv = document.createElement("div");
    loadingDiv.id = "face-loading-indicator";
    loadingDiv.style.position = "fixed";
    loadingDiv.style.top = "0";
    loadingDiv.style.left = "0";
    loadingDiv.style.width = "100vw";
    loadingDiv.style.height = "100vh";
    loadingDiv.style.background = "rgba(0,0,0,0.5)";
    loadingDiv.style.display = "flex";
    loadingDiv.style.alignItems = "center";
    loadingDiv.style.justifyContent = "center";
    loadingDiv.style.zIndex = "9999";
    loadingDiv.style.color = "#fff";
    loadingDiv.style.fontSize = "2rem";
    loadingDiv.innerText = "Loading face data...";
    document.body.appendChild(loadingDiv);

    // Load models
    await Promise.all([
        faceapi.nets.ssdMobilenetv1.loadFromUri(modelsPath),
        faceapi.nets.faceRecognitionNet.loadFromUri(modelsPath),
        faceapi.nets.faceLandmark68Net.loadFromUri(modelsPath),
    ]);

    // Start webcam
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        videoElement.srcObject = stream;
    } catch (err) {
        console.error("Camera error:", err);
        return;
    }

    // Prepare labeled faces
    async function getLabeledFaceDescriptions() {
        return Promise.all(
            labels.map(async (label) => {
                const descriptions = [];
                for (let i = 1; i <= 5; i++) {
                    const img = await faceapi.fetchImage(`${imagesPath}/${label}/${i}.jpg`);
                    const det = await faceapi
                        .detectSingleFace(img)
                        .withFaceLandmarks()
                        .withFaceDescriptor();
                    if (det) descriptions.push(det.descriptor);
                }
                return new faceapi.LabeledFaceDescriptors(label, descriptions);
            })
        );
    }

    videoElement.addEventListener("play", async () => {
        const labeledFaceDescriptors = await getLabeledFaceDescriptions();
        loadingDiv.remove();
        const matcher = new faceapi.FaceMatcher(labeledFaceDescriptors);

        const canvas = faceapi.createCanvasFromMedia(videoElement);
        document.body.append(canvas);

        const size = { width: videoElement.width, height: videoElement.height };
        faceapi.matchDimensions(canvas, size);

        setInterval(async () => {
            const detections = await faceapi
                .detectAllFaces(videoElement)
                .withFaceLandmarks()
                .withFaceDescriptors();

            const resized = faceapi.resizeResults(detections, size);
            canvas.getContext("2d").clearRect(0, 0, canvas.width, canvas.height);

            const results = resized.map(d => matcher.findBestMatch(d.descriptor));
            for (let i = 0; i < results.length; i++) {
                const label = results[i].toString();
                if (label.includes("unknown")) continue;
                if (i > 0) break;

                const box = resized[i].detection.box;
                const drawBox = new faceapi.draw.DrawBox(box, { label });
                drawBox.draw(canvas);

                onDetect(label, resized[i].detection);
                break;
            }
        }, 100);
    });

}
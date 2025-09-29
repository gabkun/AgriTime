async function loadFaceDetection(videoElement, labels, options = {}) {
    const modelsPath = options.modelsPath || "models";
    const imagesPath = options.imagesPath || "labels";
    const onDetect = options.onDetect || (() => { });

    // Show loading indicator
    const loadingDiv = document.createElement("div");
    loadingDiv.id = "face-loading-indicator";
    Object.assign(loadingDiv.style, {
        position: "fixed",
        top: "0",
        left: "0",
        width: "100vw",
        height: "100vh",
        background: "rgba(0,0,0,0.5)",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        zIndex: "9999",
        color: "#fff",
        fontSize: "2rem"
    });
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

    // Prepare face descriptors
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

    videoElement.addEventListener("loadedmetadata", async () => {
        const labeledFaceDescriptors = await getLabeledFaceDescriptions();
        loadingDiv.remove();

        const matcher = new faceapi.FaceMatcher(labeledFaceDescriptors);

        // ✅ Append canvas to #video-container
        const canvas = faceapi.createCanvasFromMedia(videoElement);
        const container = document.getElementById("video-container");
        container.appendChild(canvas);

        // ✅ Dynamically set canvas size based on video
        const displaySize = {
            width: videoElement.videoWidth || videoElement.width,
            height: videoElement.videoHeight || videoElement.height,
        };
        faceapi.matchDimensions(canvas, displaySize);

        // Resize canvas to match video
        canvas.width = displaySize.width;
        canvas.height = displaySize.height;

        // Start detecting
        setInterval(async () => {
            const detections = await faceapi
                .detectAllFaces(videoElement)
                .withFaceLandmarks()
                .withFaceDescriptors();

            const resized = faceapi.resizeResults(detections, displaySize);
            const ctx = canvas.getContext("2d");
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            // resized.forEach((result) => { //raphael(0.5)
            //     const label = matcher.findBestMatch(result.descriptor).toString();
            //     const drawBox = new faceapi.draw.DrawBox(result.detection.box, { label });
            //     drawBox.draw(canvas);

            //     if (!label.includes("unknown")) {
            //         onDetect(label, result.detection);
            //     }
            // });

            resized.forEach((result) => {
                const bestMatch = matcher.findBestMatch(result.descriptor);
                const label = bestMatch.label; // only name without confidence score
                const drawBox = new faceapi.draw.DrawBox(result.detection.box, { label });
                drawBox.draw(canvas);

                if (label !== "unknown") {
                    onDetect(label, result.detection);
                }
            });


        }, 100);
    });
}

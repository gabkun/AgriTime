async function loadFaceDetection(videoElement, labels, options = {}) {
    const modelsPath = options.modelsPath || "models";
    const imagesPath = options.imagesPath || "labels";
    const onDetect = options.onDetect || (() => { });

    // 🔹 Loading overlay
    const loadingDiv = document.createElement("div");
    Object.assign(loadingDiv.style, {
        position: "fixed",
        top: 0,
        left: 0,
        width: "100vw",
        height: "100vh",
        background: "rgba(0,0,0,0.5)",
        display: "flex",
        alignItems: "center",
        justifyContent: "center",
        zIndex: 9999,
        color: "#fff",
        fontSize: "2rem",
    });
    loadingDiv.textContent = "Loading face data...";
    document.body.appendChild(loadingDiv);

    // 🔹 Load models
    await Promise.all([
        faceapi.nets.ssdMobilenetv1.loadFromUri(modelsPath),
        faceapi.nets.faceRecognitionNet.loadFromUri(modelsPath),
        faceapi.nets.faceLandmark68Net.loadFromUri(modelsPath),
    ]);

    // 🔹 Start webcam
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: true });
        videoElement.srcObject = stream;
    } catch (err) {
        console.error("Camera error:", err);
        loadingDiv.remove();
        return;
    }

    // 🔹 Prepare face descriptors
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

        // ✅ Ensure container exists
        const container = document.getElementById("video-container");
        if (!container) {
            console.error("Missing #video-container in HTML");
            return;
        }

        // ✅ Create canvas manually and overlay
        const canvas = document.createElement("canvas");
        canvas.id = "overlay";
        Object.assign(canvas.style, {
            position: "absolute",
            top: "0",
            left: "0",
            zIndex: "2",
        });
        container.appendChild(canvas);

        // ✅ Sync canvas with actual displayed video size
        const updateCanvasSize = () => {
            const rect = videoElement.getBoundingClientRect();
            canvas.width = rect.width;
            canvas.height = rect.height;
        };
        updateCanvasSize();
        window.addEventListener("resize", updateCanvasSize);

        // 🔹 Start face detection
        setInterval(async () => {
            const detections = await faceapi
                .detectAllFaces(videoElement)
                .withFaceLandmarks()
                .withFaceDescriptors();

            const displaySize = {
                width: videoElement.videoWidth,
                height: videoElement.videoHeight,
            };
            faceapi.matchDimensions(canvas, displaySize);

            const resized = faceapi.resizeResults(detections, displaySize);
            const ctx = canvas.getContext("2d");
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            resized.forEach((result) => {
                const bestMatch = matcher.findBestMatch(result.descriptor);
                const label = bestMatch.label;
                const drawBox = new faceapi.draw.DrawBox(result.detection.box, {
                    label: label !== "unknown" ? label : "Unknown 🤨",
                    boxColor: label !== "unknown" ? "#00e676" : "#ff1744",
                    lineWidth: 3,
                });
                drawBox.draw(canvas);

                if (label !== "unknown") {
                    onDetect(label, result.detection);
                }
            });
        }, 100);
    });
}

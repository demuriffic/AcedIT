import cv2
import sys
import os

print("Python script started")

# # Handle no input path
# if len(sys.argv) < 2:
#     print("No image path provided.")
#     sys.exit(1)

# image_path = sys.argv[1]

# # Check file exists
# if not os.path.exists(image_path):
#     print(f"Image not found: {image_path}")
#     sys.exit(1)

# # Load Haarcascade
# face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + "haarcascade_frontalface_default.xml")

# # Read image
# image = cv2.imread(image_path)
# if image is None:
#     print("Failed to load image.")
#     sys.exit(1)

# gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
# faces = face_cascade.detectMultiScale(gray, scaleFactor=1.1, minNeighbors=5)

# if len(faces) == 0:
#     print("No faces detected.")
# else:
#     print(f"{len(faces)} face(s) detected.")

import os
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3'  # Suppress TensorFlow logs

import sys
from tensorflow.keras.models import load_model
from tensorflow.keras.preprocessing import image
import numpy as np

# Define class labels in the same order as in training
class_labels = ['AI-Generated', 'Authentic', 'Tampered', ]

# Suppress absl logging
import logging
logging.getLogger('absl').setLevel(logging.ERROR)

model = load_model('vgg19_se_model.h5')

img_path = sys.argv[1]
img = image.load_img(img_path, target_size=(150, 150))
x = image.img_to_array(img)
x = np.expand_dims(x, axis=0)
x = x / 255.0

# Predict
prediction = model.predict(x, batch_size=1, verbose=0)
predicted_class = np.argmax(prediction[0])
confidence = prediction[0][predicted_class]

# Output result
label = class_labels[predicted_class]
print(f"Prediction: {label} (Confidence: {confidence:.4f})\n")
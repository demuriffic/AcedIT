import warnings
warnings.filterwarnings("ignore")
import os
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '3'
import sys
sys.stderr = open(os.devnull, 'w')

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
confidence = confidence*100
print(f"{confidence:.2f}% {label}\n")

# HEATMAP GENERATION
import cv2
import matplotlib.pyplot as plt
import tensorflow as tf

def make_gradcam_heatmap(img_array, model, last_conv_layer_name, pred_index=None):
    grad_model = tf.keras.models.Model(
        [model.inputs], [model.get_layer(last_conv_layer_name).output, model.output]
    )
    with tf.GradientTape() as tape:
        conv_outputs, predictions = grad_model(img_array)
        if pred_index is None:
            pred_index = tf.argmax(predictions[0])
        class_channel = predictions[:, pred_index]
    grads = tape.gradient(class_channel, conv_outputs)
    pooled_grads = tf.reduce_mean(grads, axis=(0, 1, 2))
    conv_outputs = conv_outputs[0]
    heatmap = conv_outputs @ pooled_grads[..., tf.newaxis]
    heatmap = tf.squeeze(heatmap)
    heatmap = tf.maximum(heatmap, 0) / tf.math.reduce_max(heatmap)
    return heatmap.numpy()

import glob
import os

# ...existing code...
if label in ['AI-Generated', 'Tampered']:
    last_conv_layer_name = 'block5_conv4'
    heatmap = make_gradcam_heatmap(x, model, last_conv_layer_name, pred_index=predicted_class)
    img_orig = cv2.imread(img_path)
    orig_height, orig_width = img_orig.shape[:2]
    heatmap_resized = cv2.resize(heatmap, (orig_width, orig_height))
    heatmap_uint8 = np.uint8(255 * heatmap_resized)
    heatmap_color = cv2.applyColorMap(heatmap_uint8, cv2.COLORMAP_JET)
    superimposed_img = cv2.addWeighted(img_orig, 0.6, heatmap_color, 0.4, 0)

    # Find the region with the highest activation
    min_val, max_val, min_loc, max_loc = cv2.minMaxLoc(heatmap_resized)
    # Draw a rectangle (20x20) around the max activation point
    top_left = (max(0, max_loc[0] - 100), max(0, max_loc[1] - 100))
    bottom_right = (min(orig_width, max_loc[0] + 100), min(orig_height, max_loc[1] + 100))
    cv2.rectangle(superimposed_img, top_left, bottom_right, (0, 255, 0), 10)

    
    # Save the result
    heatmap_dir = "heatmap"
    if not os.path.exists(heatmap_dir):
        os.makedirs(heatmap_dir)
    base_name = os.path.splitext(os.path.basename(img_path))[0]
    heatmap_path = os.path.join(heatmap_dir, f"heatmap_{base_name}.jpg")
    cv2.imwrite(heatmap_path, superimposed_img)
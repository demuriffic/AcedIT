// async function hashFile(file) {
//   const arrayBuffer = await file.arrayBuffer();
//   const hashBuffer = await crypto.subtle.digest('SHA-256', arrayBuffer);
//   return Array.from(new Uint8Array(hashBuffer)).map(b => b.toString(16).padStart(2, '0')).join('');
// }

const dropArea = document.getElementById('drop-area');
const fileInput = document.getElementById('fileElem');
const preview = document.getElementById('preview');
const resultText = document.getElementById('resultText');
const uploadForm = document.querySelector('.upload-form');

let selectedFiles = null;

// Utility to show/hide result text
function showResultText() {
  if (resultText) resultText.style.display = 'block';
}
function hideResultText() {
  if (resultText) resultText.style.display = 'none';
}

// Drag & drop events (desktop only)
['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
  dropArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
  e.preventDefault();
  e.stopPropagation();
}

dropArea.addEventListener('dragover', () => dropArea.classList.add('highlight'));
dropArea.addEventListener('dragleave', () => dropArea.classList.remove('highlight'));
dropArea.addEventListener('drop', handleDrop);

function handleDrop(e) {
  dropArea.classList.remove('highlight');
  const dt = e.dataTransfer;
  selectedFiles = dt.files;
  previewSelectedImage(selectedFiles);
}

fileInput.addEventListener('change', (e) => {
  selectedFiles = e.target.files;
  previewSelectedImage(selectedFiles);
});

function previewSelectedImage(files) {
  let foundImage = false;
  [...files].forEach(file => {
    if (file.type.startsWith('image/')) {
      foundImage = true;
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.innerHTML = ""; // Clear preview
        const img = document.createElement('img');
        img.src = e.target.result;
        img.alt = "Preview";
        img.style.maxWidth = "100%";
        img.style.maxHeight = "300px";
        img.style.borderRadius = "8px";
        img.style.boxShadow = "0 2px 12px 0 rgba(18,18,18,0.25)";
        img.style.border = "1px solid #FFFFFF22";
        preview.appendChild(img);
        preview.appendChild(resultText);
        showResultText();
        document.getElementById('result').scrollIntoView({behavior: 'smooth'});
      };
      reader.readAsDataURL(file);
    }
  });
  if (!foundImage) {
    preview.innerHTML = "<p style='color:red;'>Only image files are allowed.</p>";
    hideResultText();
  }
}

// Handle form submit
uploadForm.addEventListener('submit', function(e) {
  // Let the form submit normally to PHP
  if (!selectedFiles || selectedFiles.length === 0) {
    preview.innerHTML = "<p style='color:red;'>Please select an image file before submitting.</p>";
    hideResultText();
    e.preventDefault();
    return;
  }
});

hideResultText(); // Hide result text initially
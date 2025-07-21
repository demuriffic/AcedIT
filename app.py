from flask import Flask, jsonify, send_from_directory, request
from flask_cors import CORS

import sqlite3

app = Flask(__name__, static_folder='.')
CORS(app)
def get_stats():
    conn = sqlite3.connect('stats.db')
    c = conn.cursor()
    c.execute('SELECT SUM(total_tests), SUM(fake_receipts), SUM(real_receipts) FROM stats')
    row = c.fetchone()
    conn.close()
    return {
        "totalTests": row[0] or 0,
        "fakeReceipts": row[1] or 0,
        "realReceipts": row[2] or 0
    }

@app.route('/api/stats')
def api_stats():
    return jsonify(get_stats())

@app.route('/api/increment', methods=['POST'])
def increment_total_tests():
    conn = sqlite3.connect('stats.db')
    c = conn.cursor()
    # Update the first row (id=1); adjust as needed for your schema
    c.execute('UPDATE stats SET total_tests = total_tests + 1 WHERE id=1')
    conn.commit()
    conn.close()
    return jsonify({"success": True})

# Serve static files (HTML, JS, CSS)
@app.route('/')
def index():
    return send_from_directory('.', 'index.html')

@app.route('/<path:path>')
def static_proxy(path):
    return send_from_directory('.', path)

if __name__ == '__main__':
    app.run(debug=True)
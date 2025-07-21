import sqlite3

conn = sqlite3.connect('stats.db')
c = conn.cursor()
c.execute('''
    CREATE TABLE IF NOT EXISTS stats (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        total_tests INTEGER,
        fake_receipts INTEGER,
        real_receipts INTEGER
    )
''')
# Insert demo data (run only once)
c.execute('INSERT INTO stats (total_tests, fake_receipts, real_receipts) VALUES (?, ?, ?)', (0, 0, 0))
conn.commit()
conn.close()
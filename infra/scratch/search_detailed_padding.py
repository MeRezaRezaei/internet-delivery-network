import re

file_path = r"C:\Users\MeRezaRezaei\.gemini\antigravity\brain\715e8ed1-55d5-44fa-8544-5265b3cc2d3b\.system_generated\steps\1902\content.md"

with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

# Let's clean the HTML tags
def clean_html(raw_html):
    cleanr = re.compile('<.*?>')
    return re.sub(cleanr, ' ', raw_html)

cleaned = clean_html(content)
lines = cleaned.split('\n')
non_empty = [line.strip() for line in lines if line.strip()]

# Search for any line containing 'padding' or 'Referer' and see what issues people had
results = []
for i, line in enumerate(non_empty):
    if "padding" in line.lower() or "referer" in line.lower():
        start = max(0, i - 8)
        end = min(len(non_empty), i + 12)
        results.append(f"=== MATCH {i} ===\n" + "\n".join(f"{j}: {non_empty[j]}" for j in range(start, end)))

with open(r"C:\Users\MeRezaRezaei\.gemini\antigravity\brain\715e8ed1-55d5-44fa-8544-5265b3cc2d3b\scratch\detailed_padding_search.txt", "w", encoding="utf-8") as f:
    f.write("\n\n".join(results))

print(f"Wrote {len(results)} matches to detailed_padding_search.txt")

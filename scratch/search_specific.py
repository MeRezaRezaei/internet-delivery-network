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

# Search specifically for "invalid padding" or similar errors in discussion posts or comments
keywords = ["invalid padding", "length:0", "length", "padding", "xPaddingBytes", "xPaddingObfsMode"]
results = []
for i, line in enumerate(non_empty):
    if "invalid padding" in line.lower() or "length:0" in line.lower():
        start = max(0, i - 10)
        end = min(len(non_empty), i + 10)
        results.append(f"=== MATCH {i} ===")
        for j in range(start, end):
            results.append(f"{j}: {non_empty[j]}")
        results.append("\n" + "="*40 + "\n")

if not results:
    # If not found, let's search for "padding" and print more context
    for i, line in enumerate(non_empty):
        if "padding" in line.lower() and ("error" in line.lower() or "issue" in line.lower() or "fail" in line.lower() or "invalid" in line.lower()):
            start = max(0, i - 10)
            end = min(len(non_empty), i + 10)
            results.append(f"=== PADDING MATCH {i} ===")
            for j in range(start, end):
                results.append(f"{j}: {non_empty[j]}")
            results.append("\n" + "="*40 + "\n")

with open(r"C:\Users\MeRezaRezaei\.gemini\antigravity\brain\715e8ed1-55d5-44fa-8544-5265b3cc2d3b\scratch\specific_results.txt", "w", encoding="utf-8") as f:
    f.write("\n".join(results))

print(f"Wrote {len(results)} matches to specific_results.txt")

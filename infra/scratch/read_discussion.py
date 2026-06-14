import re

file_path = r"C:\Users\MeRezaRezaei\.gemini\antigravity\brain\715e8ed1-55d5-44fa-8544-5265b3cc2d3b\.system_generated\steps\1902\content.md"
output_path = r"C:\Users\MeRezaRezaei\.gemini\antigravity\brain\715e8ed1-55d5-44fa-8544-5265b3cc2d3b\scratch\results.txt"

with open(file_path, "r", encoding="utf-8") as f:
    content = f.read()

# Let's clean the HTML tags to make it readable markdown/text
def clean_html(raw_html):
    # Surgical removal of tags but preserving text
    cleanr = re.compile('<.*?>')
    cleantext = re.sub(cleanr, ' ', raw_html)
    return cleantext

cleaned = clean_html(content)
lines = cleaned.split('\n')
non_empty = [line.strip() for line in lines if line.strip()]

# Search for key terms
keywords = ["padding", "obfs", "xPaddingBytes", "xPaddingObfsMode", "invalid padding", "mode"]
results = []
for i, line in enumerate(non_empty):
    if any(kw in line for kw in keywords):
        start = max(0, i - 15)
        end = min(len(non_empty), i + 15)
        results.append(f"=== MATCH {i} ({line[:40]}) ===")
        for j in range(start, end):
            results.append(f"{j}: {non_empty[j]}")
        results.append("\n" + "="*40 + "\n")

with open(output_path, "w", encoding="utf-8") as f:
    f.write("\n".join(results))

print("Successfully wrote results to results.txt")

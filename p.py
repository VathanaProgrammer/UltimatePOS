import os
import re
import time

def split_columns_top_level(text):
    """Split columns by top-level commas, ignoring commas inside parentheses and quotes."""
    cols = []
    buf = []
    parens = 0
    in_quote = False
    for ch in text:
        if ch == "'" and (not buf or buf[-1] != "\\"):
            in_quote = not in_quote
        if not in_quote:
            if ch == "(":
                parens += 1
            elif ch == ")":
                parens = max(0, parens - 1)
            if ch == "," and parens == 0:
                cols.append("".join(buf).strip())
                buf = []
                continue
        buf.append(ch)
    last = "".join(buf).strip()
    if last:
        cols.append(last)
    return cols

def parse_column(col_def):
    """Parse a column definition into name, type, extras."""
    col_def = col_def.strip()
    if not col_def.startswith("`"):
        return None
    m = re.match(r'`([^`]*)`\s+(.*)', col_def, re.S)
    if not m:
        return None
    name, rest = m.groups()
    # extract type with balanced parentheses
    dtype = ""
    extras = ""
    parens = 0
    for i, ch in enumerate(rest):
        dtype += ch
        if ch == "(":
            parens += 1
        elif ch == ")":
            parens -= 1
            if parens == 0:
                dtype = dtype.strip()
                extras = rest[i+1:].strip()
                break
    else:
        dtype = rest.strip()
        extras = ""
    return name, dtype, extras

def clean_default(dtype, default_raw):
    if default_raw is None:
        return None
    val = default_raw.strip()
    if val.upper() == "NULL":
        return "NULL"
    if dtype.lower().startswith(("varchar","char","text","enum")):
        val = val.strip("'\"")
        return f'"{val}"'
    return val

def sql_to_dbml(sql_text):
    dbml_lines = []
    tables = re.findall(r'CREATE TABLE\s+`([^`]+)`\s*\((.*?)\)\s*[^;]*;', sql_text, re.S | re.I)
    total = len(tables)
    for idx, (table_name, columns_block) in enumerate(tables, start=1):
        print(f"[{idx}/{total}] Processing table: {table_name} ...")
        time.sleep(0.01)
        dbml_lines.append(f"Table {table_name} {{")
        cols = split_columns_top_level(columns_block)
        for col in cols:
            parsed = parse_column(col)
            if not parsed:
                continue
            name, dtype, extras = parsed
            line = f"  {name} {dtype}"
            if re.search(r'\bNOT NULL\b', extras, re.I):
                line += " [not null]"
            if re.search(r'\bAUTO_INCREMENT\b', extras, re.I):
                line += " [increment]"
            mdef = re.search(r"\bDEFAULT\b\s+((NULL)|('(?:[^']|\\')*')|(\"(?:[^\"]|\\\")*\")|[^\s,]+)", extras, re.I)
            if mdef:
                default_val = clean_default(dtype, mdef.group(1))
                if default_val:
                    line += f" [default: {default_val}]"
            dbml_lines.append(line)
        dbml_lines.append("}\n")
    # foreign keys
    fks = re.findall(r'CONSTRAINT\s+`[^`]+`\s+FOREIGN KEY\s*\(`([^`]+)`\)\s+REFERENCES\s+`([^`]+)`\s*\(`([^`]+)`\)', sql_text, re.I)
    for fk_col, ref_table, ref_col in fks:
        dbml_lines.append(f"Ref: {fk_col} > {ref_table}.{ref_col}")
    return "\n".join(dbml_lines)

def main():
    input_path = input("Enter full path to SQL file: ").strip()
    if not input_path or not os.path.exists(input_path):
        print("File not found:", input_path)
        return
    output_path = input("Enter full path for DBML output (e.g., D:/output.dbml): ").strip()
    if not output_path:
        output_path = "D:/output.dbml"
    os.makedirs(os.path.dirname(output_path), exist_ok=True)
    with open(input_path, "r", encoding="utf-8", errors="ignore") as f:
        sql_text = f.read()
    dbml = sql_to_dbml(sql_text)
    with open(output_path, "w", encoding="utf-8") as f:
        f.write(dbml)
    print("Done. DBML saved to:", output_path)

if __name__ == "__main__":
    main()

# -*- coding: utf-8 -*-
"""Convert flat CSS to nested SCSS (hyphen BEM) without changing declarations."""
from pathlib import Path
from collections import defaultdict
import re

ROOT = Path(__file__).resolve().parents[1]
SRC = ROOT / "assets" / "css" / "style.css"
DST = ROOT / "assets" / "scss" / "past.scss"


def strip_comments(text: str) -> str:
    out = []
    i = 0
    n = len(text)
    while i < n:
        if text.startswith("/*", i):
            j = text.find("*/", i + 2)
            if j < 0:
                break
            i = j + 2
            continue
        out.append(text[i])
        i += 1
    return "".join(out)


def parse_blocks(s: str):
    blocks = []
    i = 0
    n = len(s)
    while i < n:
        while i < n and s[i].isspace():
            i += 1
        if i >= n:
            break
        if s[i] == "@":
            start = i
            j = i
            while j < n and s[j] not in "{;":
                j += 1
            if j >= n:
                break
            if s[j] == ";":
                blocks.append(("atrule_semi", s[start : j + 1].strip()))
                i = j + 1
                continue
            depth = 0
            k = j
            while k < n:
                if s[k] == "{":
                    depth += 1
                elif s[k] == "}":
                    depth -= 1
                    if depth == 0:
                        k += 1
                        break
                k += 1
            header = s[start:j].strip()
            body = s[j + 1 : k - 1]
            blocks.append(("atrule", header, body))
            i = k
            continue
        start = i
        while i < n and s[i] != "{":
            i += 1
        if i >= n:
            break
        selector = s[start:i].strip()
        depth = 0
        j = i
        while j < n:
            if s[j] == "{":
                depth += 1
            elif s[j] == "}":
                depth -= 1
                if depth == 0:
                    j += 1
                    break
            j += 1
        body = s[i + 1 : j - 1]
        blocks.append(("rule", selector, body, None))
        i = j
    return blocks


def indent_props(body: str, level: int = 1) -> str:
    props = []
    for line in body.split(";"):
        line = line.strip()
        if line:
            props.append(("  " * level) + line + ";")
    return "\n".join(props)


def normalize_decls(body: str):
    return [p.strip() for p in body.split(";") if p.strip()]


def expand_blocks(blocks):
    out = []
    for b in blocks:
        if b[0] == "atrule_semi":
            out.append(b)
            continue
        if b[0] == "atrule":
            header, body = b[1], b[2]
            if header.lower().startswith("@media"):
                for ib in parse_blocks(body):
                    if ib[0] == "rule":
                        out.append(("rule", ib[1], ib[2], header))
                    else:
                        out.append(ib)
            else:
                out.append(("raw", header + " {\n" + indent_props(body) + "\n}\n"))
            continue
        out.append(b)
    return out


def split_selectors(sel: str):
    parts = []
    cur = []
    depth = 0
    for ch in sel:
        if ch in "([":
            depth += 1
            cur.append(ch)
        elif ch in ")]":
            depth = max(0, depth - 1)
            cur.append(ch)
        elif ch == "," and depth == 0:
            parts.append("".join(cur).strip())
            cur = []
        else:
            cur.append(ch)
    if cur:
        parts.append("".join(cur).strip())
    return [p for p in parts if p]


def split_hyphen_parts(name: str):
    if "__" in name:
        root, _, rest = name.partition("__")
        return [root] + (["__" + rest] if rest else [])
    return re.split(r"(?<!-)-(?!-)", name)


def parse_leading_class_chain(sel: str):
    if not sel.startswith("."):
        return None

    i = 0
    n = len(sel)
    classes = []
    while i < n and sel[i] == ".":
        i += 1
        start = i
        while i < n and (sel[i].isalnum() or sel[i] in "_-"):
            i += 1
        if i == start:
            return None
        classes.append(sel[start:i])

    pseudo_start = i
    while i < n and sel[i] == ":":
        i += 1
        if i < n and sel[i] == ":":
            i += 1
        while i < n and (sel[i].isalnum() or sel[i] in "_-"):
            i += 1
        if i < n and sel[i] == "(":
            depth = 0
            while i < n:
                if sel[i] == "(":
                    depth += 1
                elif sel[i] == ")":
                    depth -= 1
                    i += 1
                    if depth == 0:
                        break
                    continue
                i += 1
    pseudos = sel[pseudo_start:i]

    while i < n and sel[i].isspace():
        i += 1
    combinator = ""
    rest = ""
    if i < n:
        if sel[i] in {">", "+", "~"}:
            combinator = f" {sel[i]} "
            i += 1
            while i < n and sel[i].isspace():
                i += 1
            rest = sel[i:].strip()
        else:
            combinator = " "
            rest = sel[i:].strip()

    return {
        "classes": classes,
        "pseudos": pseudos,
        "combinator": combinator,
        "rest": rest,
    }


def empty_node():
    return {
        "decls": defaultdict(list),
        "pseudos": defaultdict(lambda: defaultdict(list)),
        "compounds": {},
        "children": {},
        "descendants": [],
    }


def ensure_child(parent, key):
    if key not in parent["children"]:
        parent["children"][key] = empty_node()
    return parent["children"][key]


def ensure_compound(parent, cls):
    if cls not in parent["compounds"]:
        parent["compounds"][cls] = empty_node()
    return parent["compounds"][cls]


def walk_parts(forest, parts):
    root = parts[0]
    if root not in forest:
        forest[root] = empty_node()
    node = forest[root]
    for part in parts[1:]:
        node = ensure_child(node, part)
    return node


def emit_decls(decls_by_media, indent):
    lines = []
    pad = "  " * indent
    if decls_by_media.get(""):
        for d in decls_by_media[""]:
            lines.append(f"{pad}{d};")
    for media, decls in decls_by_media.items():
        if media == "" or not decls:
            continue
        lines.append(f"{pad}{media} {{")
        for d in decls:
            lines.append(f"{pad}  {d};")
        lines.append(f"{pad}}}")
    return lines


def add_rule_to_node(node, parsed, decls, media):
    cur = node
    for extra in parsed["classes"][1:]:
        cur = ensure_compound(cur, extra)

    rest = parsed["rest"]
    combinator = parsed["combinator"]
    pseudos = parsed["pseudos"]

    if rest:
        if combinator.strip() in {">", "+", "~"}:
            descendant_sel = f"{combinator.strip()} {rest}"
        else:
            descendant_sel = rest
        cur["descendants"].append(
            {
                "sel": descendant_sel,
                "decls": decls,
                "media": media,
                "pseudo": pseudos,
            }
        )
        return

    if pseudos:
        cur["pseudos"][pseudos][media].extend(decls)
    else:
        cur["decls"][media].extend(decls)


def emit_node(name, node, indent, is_root=False, compound=False):
    lines = []
    pad = "  " * indent
    if is_root:
        lines.append(f"{pad}.{name} {{")
    elif compound:
        lines.append(f"{pad}&.{name} {{")
    elif name.startswith("__") or name.startswith("--"):
        lines.append(f"{pad}&{name} {{")
    else:
        lines.append(f"{pad}&-{name} {{")

    lines.extend(emit_decls(node["decls"], indent + 1))

    for pseudo, media_map in sorted(node["pseudos"].items()):
        pad2 = "  " * (indent + 1)
        lines.append(f"{pad2}&{pseudo} {{")
        lines.extend(emit_decls(media_map, indent + 2))
        lines.append(f"{pad2}}}")

    for d in node["descendants"]:
        pad2 = "  " * (indent + 1)
        inner_sel = d["sel"]
        media = d["media"] or ""
        pseudo = d.get("pseudo") or ""

        def emit_inner(base_indent):
            p = "  " * base_indent
            chunk = []
            if media:
                chunk.append(f"{p}{media} {{")
                chunk.append(f"{p}  {inner_sel} {{")
                for decl in d["decls"]:
                    chunk.append(f"{p}    {decl};")
                chunk.append(f"{p}  }}")
                chunk.append(f"{p}}}")
            else:
                chunk.append(f"{p}{inner_sel} {{")
                for decl in d["decls"]:
                    chunk.append(f"{p}  {decl};")
                chunk.append(f"{p}}}")
            return chunk

        if pseudo:
            lines.append(f"{pad2}&{pseudo} {{")
            lines.extend(emit_inner(indent + 2))
            lines.append(f"{pad2}}}")
        else:
            lines.extend(emit_inner(indent + 1))

    for cname in sorted(node["compounds"].keys()):
        lines.extend(emit_node(cname, node["compounds"][cname], indent + 1, False, True))
        lines.append("")

    def child_key(k):
        if k.startswith("--"):
            return (2, k)
        if k.startswith("__"):
            return (0, k)
        return (1, k)

    for cname in sorted(node["children"].keys(), key=child_key):
        lines.extend(emit_node(cname, node["children"][cname], indent + 1, False))
        lines.append("")

    while lines and lines[-1] == "":
        lines.pop()

    lines.append(f"{pad}}}")
    return lines


def main():
    css = SRC.read_text(encoding="utf-8", errors="replace")
    text = strip_comments(css)
    expanded = expand_blocks(parse_blocks(text))

    rules = []
    raws = []
    for b in expanded:
        if b[0] == "raw":
            raws.append(b[1])
            continue
        if b[0] == "atrule_semi":
            raws.append(b[1] + "\n")
            continue
        if b[0] != "rule":
            continue
        sel, body, media = b[1], b[2], b[3]
        decls = normalize_decls(body)
        if not decls:
            continue
        for p in split_selectors(sel):
            rules.append({"sel": p, "decls": decls[:], "media": media})

    forest = {}
    flat_rules = []

    for r in rules:
        sel = r["sel"]
        media = r["media"] or ""
        decls = r["decls"]
        parsed = parse_leading_class_chain(sel)
        if not parsed or not parsed["classes"]:
            flat_rules.append(r)
            continue

        primary = parsed["classes"][0]
        parts = split_hyphen_parts(primary)
        node = walk_parts(forest, parts)
        add_rule_to_node(node, parsed, decls, media)

    out_lines = [
        "// Converted from assets/css/style.css — nested SCSS, declarations unchanged.",
        "",
    ]

    for raw in raws:
        out_lines.append(raw.rstrip())
        out_lines.append("")

    for root in sorted(forest.keys()):
        out_lines.extend(emit_node(root, forest[root], 0, True))
        out_lines.append("")

    out_lines.append("// --- base & element selectors ---")
    out_lines.append("")
    out_lines.append("* {")
    out_lines.append("  font-family: 'Century Gothic';")
    out_lines.append("}")
    out_lines.append("")
    out_lines.append("body {")
    out_lines.append("  margin: 0;")
    out_lines.append("  padding: 0;")
    out_lines.append("")
    out_lines.append("  &.menu-active {")
    out_lines.append("    @media screen and (max-width: 767px) {")
    out_lines.append("      overflow-y: hidden;")
    out_lines.append("    }")
    out_lines.append("  }")
    out_lines.append("}")
    out_lines.append("")
    out_lines.append("button {")
    out_lines.append("  cursor: pointer;")
    out_lines.append("}")
    out_lines.append("")
    out_lines.append("h1,")
    out_lines.append("h2,")
    out_lines.append("h3,")
    out_lines.append("h4,")
    out_lines.append("h5,")
    out_lines.append("h6 {")
    out_lines.append("  i {")
    out_lines.append("    font-style: normal;")
    out_lines.append("    color: #808A9C;")
    out_lines.append("  }")
    out_lines.append("}")
    out_lines.append("")
    out_lines.append("a.reviews-tab-text-item-source {")
    out_lines.append("  &:hover {")
    out_lines.append("    text-decoration: underline;")
    out_lines.append("  }")
    out_lines.append("}")
    out_lines.append("")

    known = {
        "*",
        "body",
        "button",
        "h1 i",
        "h2 i",
        "h3 i",
        "h4 i",
        "h5 i",
        "h6 i",
        "a.reviews-tab-text-item-source:hover",
        "body.menu-active",
    }
    leftovers = [r for r in flat_rules if r["sel"] not in known]
    if leftovers:
        out_lines.append("// --- selectors that were not auto-nested ---")
        out_lines.append("")
        for r in leftovers:
            media = r["media"]
            if media:
                out_lines.append(f"{media} {{")
                out_lines.append(f'  {r["sel"]} {{')
                for d in r["decls"]:
                    out_lines.append(f"    {d};")
                out_lines.append("  }")
                out_lines.append("}")
            else:
                out_lines.append(f'{r["sel"]} {{')
                for d in r["decls"]:
                    out_lines.append(f"  {d};")
                out_lines.append("}")
            out_lines.append("")

    DST.write_text("\n".join(out_lines) + "\n", encoding="utf-8")
    print(f"parsed rules: {len(rules)}")
    print(f"forest roots: {len(forest)}")
    print(f"flat: {len(flat_rules)} leftovers: {len(leftovers)}")
    print(f"wrote {DST} ({len(out_lines)} lines)")
    roots = sorted(forest.keys())
    print("has certificates:", "certificates" in forest)
    print("has hero:", "hero" in forest)
    print("sample roots:", roots[:20])


if __name__ == "__main__":
    main()

# Contributing to the Documentation

We welcome contributions to the ProcessWire Commerce documentation! This guide explains how you can help improve our docs.

## Getting Started

1.  **Fork the Repository:** Create a fork of the [ProcessWire Commerce Repository](https://github.com/kongondo/PWCommerce2Starter).
2.  **Clone Your Fork:** Clone your forked repository to your local machine.
3.  **Create a Branch:** Create a new branch for your changes (e.g., `docs/fix-typo`).
4.  **Edit Files:** Make your changes to the Markdown files in the `docs/` directory.
5.  **Commit and Push:** Commit your changes and push them to your fork.
6.  **Submit a Pull Request:** Open a Pull Request from your branch to the main repository's `dev` branch.

## Markdown Format

Documentation is written in Markdown.

## Importing from Word/Other Formats

If you have documentation in Word or other formats, you can convert it to Markdown using tools like [Pandoc](https://pandoc.org/).

### Using Pandoc

To convert a Word document (`.docx`) to Markdown:

```bash
pandoc my-doc.docx -f docx -t markdown -o my-doc.md
```

You can then copy the content of `my-doc.md` into the appropriate file in the `docs/` directory.

### Online Converters

There are also online tools available, such as [Word to Markdown Converter](https://word2md.com/).

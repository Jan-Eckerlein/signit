# PDF Thumbnails and Real-Time Updates: Architectural Context

## Overview

This document records the architectural decisions and reasoning behind how PDF page thumbnails and real-time updates are handled in this project. It is intended as a reference for future development and for explaining the current approach to new team members.

---

## Problem Statement

-   The application manages documents with potentially long PDFs.
-   Users need to see fast previews of individual pages (thumbnails) and receive real-time updates when high-resolution images are ready.
-   The system must efficiently regenerate and serve these previews whenever a user updates a field value related to a page.

---

## Solution Summary

-   **Server-side PDF thumbnail generation**: When a PDF page is (re)generated, the server also generates one or more thumbnail images for that page.
-   **On-demand resizing**: If a requested thumbnail size does not exist, the server returns a low-res placeholder and queues a job to generate the requested size.
-   **Real-time updates via WebSockets (Laravel Reverb)**: When the requested thumbnail is ready, the server notifies the client in real time, allowing the UI to swap in the high-res image without polling.

---

## Why This Approach?

### 1. **Server-side Thumbnails**

-   **Performance**: Thumbnails are instantly available for fast UI rendering, especially in lists or on mobile.
-   **Consistency**: All clients see the same preview, regardless of device/browser.
-   **Bandwidth**: Thumbnails are much smaller than full PDFs, reducing load times.

### 2. **On-demand Resizing**

-   **Storage Efficiency**: Only generate and store sizes that are actually needed.
-   **User Experience**: Users see a low-res preview immediately, and the high-res version appears as soon as it's ready.
-   **Flexibility**: New sizes can be added without reprocessing all files.

### 3. **Real-time Updates (WebSockets/Reverb)**

-   **No Polling**: The client is notified instantly when the high-res image is ready, avoiding wasteful polling.
-   **Modern UX**: Enables seamless, progressive image loading and other real-time features.
-   **Self-hosted**: Using Laravel Reverb keeps all data in-house and is easy to run in Docker/Sail.

---

## Workflow

1. **User updates a field value** related to a PDF page.
2. **Server regenerates the PDF** for that page and generates a low-res blur thumbnail (and optionally other common sizes).
3. **Client requests a thumbnail** for a page at a specific size.
    - If the size exists, the server returns it immediately.
    - If not, the server returns the low-res image and queues a job to generate the requested size.
4. **Server generates the requested size** in the background.
5. **When ready, the server broadcasts a WebSocket event** (via Reverb) to the client with the new image URL.
6. **Client swaps in the high-res image** as soon as it receives the event.

---

## Pros and Cons

| Approach           | Pros                                                | Cons                                    |
| ------------------ | --------------------------------------------------- | --------------------------------------- |
| All sizes up front | Fast for all users, simple cache logic, predictable | More storage, more upfront CPU          |
| On demand          | Less storage, flexible for new sizes                | First load is slow, more complex logic  |
| WebSockets         | Real-time, no polling, modern UX                    | Requires running Reverb/WebSocket infra |

---

## Why Not Only Client-side Rendering?

-   Client-side PDF rendering (e.g., PDF.js) is slower, requires downloading the full PDF, and is less reliable on mobile or for previews in lists.
-   Server-side thumbnails ensure fast, consistent previews for all users and use cases.

---

## Deployment Notes

-   Reverb is run as a separate service in Docker/Sail, alongside the main app and queue workers.
-   All services share the same Redis instance for broadcasting events.
-   The frontend connects to the Reverb WebSocket port for real-time updates.

---

## References

-   [Laravel Reverb Documentation](https://laravel.com/docs/10.x/broadcasting#reverb)
-   [Progressive Image Loading Patterns](https://web.dev/progressive-web-apps/)
-   [PDF.js](https://mozilla.github.io/pdf.js/)

---

**This document should be updated as the architecture evolves.**

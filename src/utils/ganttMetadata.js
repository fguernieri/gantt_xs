const GANTT_BLOCK_RE = /(?:<!--\s*)?\[gantt\]\s*:\s*(\{[\s\S]*?\})\s*(?:-->)?/i

function todayIsoDate() {
  const now = new Date()
  return now.toISOString().slice(0, 10)
}

function normalizeMetadata(raw) {
  const defaults = {
    start: todayIsoDate(),
    progress: 0,
    predecessors: [],
  }

  if (!raw || typeof raw !== 'object') {
    return defaults
  }

  const progress = Number.isFinite(raw.progress)
    ? Math.min(100, Math.max(0, Math.round(raw.progress)))
    : defaults.progress

  const predecessors = Array.isArray(raw.predecessors)
    ? raw.predecessors.map((id) => String(id)).filter(Boolean)
    : defaults.predecessors

  return {
    start: typeof raw.start === 'string' && raw.start ? raw.start : defaults.start,
    progress,
    predecessors,
  }
}

export function parseCardDescription(description) {
  const source = typeof description === 'string' ? description : ''
  const match = source.match(GANTT_BLOCK_RE)
  let metadata = normalizeMetadata(null)

  if (match && match[1]) {
    try {
      metadata = normalizeMetadata(JSON.parse(match[1]))
    } catch (err) {
      metadata = normalizeMetadata(null)
    }
  }

  const text = source.replace(GANTT_BLOCK_RE, '').trim()

  return {
    text,
    metadata,
  }
}

export function serializeCardDescription(description, metadata) {
  const source = typeof description === 'string' ? description : ''
  const baseText = source.replace(GANTT_BLOCK_RE, '').trim()
  const safeMetadata = normalizeMetadata(metadata)
  const json = JSON.stringify(safeMetadata)
  const block = `<!-- [gantt]: ${json} -->`

  if (!baseText) {
    return block
  }

  return `${baseText}\n\n${block}`
}

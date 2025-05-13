/**
 * Trackerjack - Frontend Event Tracking Helper
 */
class Trackerjack {
    /**
     * Track an event
     * 
     * @param {string} eventName - The name of the event to track
     * @param {Object} [payload={}] - Optional payload data for the event
     * @returns {Promise} - A promise that resolves when the event is tracked
     */
    static async track(eventName, payload = {}) {
        try {
            const response = await fetch('/trackerjack/events', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    event_name: eventName,
                    payload: payload,
                }),
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Failed to track event:', error);
            throw error;
        }
    }
}

// Make it available globally
window.Trackerjack = Trackerjack; 
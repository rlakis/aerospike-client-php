function receiveMessage(event)
{
  // Do we trust the sender of this message?  (might be
  // different from what we originally opened, for example).
  console.log(event);
  //if (event.origin !== "http://example.org")
  //  return;

  // event.source is popup
  // event.data is "hi there yourself!  the secret response is: rheeeeet!"
}

window.addEventListener("message", receiveMessage, false);
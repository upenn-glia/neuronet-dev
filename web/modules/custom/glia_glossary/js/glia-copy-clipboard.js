function gliaCopyClipboard() {
  /* Get the text field */
  var copyText = document.getElementById("edit-bnb-processed-text");
  /* Select the text field */
  copyText.select();
  /* Copy the text inside the text field */
  document.execCommand("copy");
  swal("Text copied to clipboard.");
}
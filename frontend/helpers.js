const loggedIn = () => {
  return localStorage.getItem("gymUser") ? true : false;
};

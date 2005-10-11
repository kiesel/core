/* This class is part of the XP framework's EAS connectivity
 *
 * $Id$
 */

package net.xp_framework.easc.server;

import java.net.ServerSocket;
import java.net.Socket;
import java.io.IOException;
import net.xp_framework.easc.server.Handler;
import net.xp_framework.easc.server.HandlerThread;
import net.xp_framework.easc.server.ServerContext;

/**
 * Server thread
 *
 * Usage:
 * <code>
 *   server= new ServerThread(new ServerSocket(6100, 50, InetAddress.getLocalHost()));
 *   server.setHandler(...);
 *   server.start();
 *
 *   // ...
 *
 *   server.shutdown();
 * </code>
 *
 * @see   net.xp_framework.easc.server.Handler
 * @see   java.net.ServerSocket
 */
public class ServerThread extends Thread {
    private boolean stopped= true;
    private ServerSocket socket= null;
    private Handler handler= null;
    
    /**
     * Constructor
     *
     * @access  public
     * @param   java.net.ServerSocket socket
     */
    public ServerThread(ServerSocket socket) {
        super("ServerThread@{" + socket.getInetAddress().toString() + ":" + socket.getLocalPort() + "}");
        this.socket= socket;
    }

    /**
     * Set handler for accepted connections
     *
     * @access  public
     * @param   net.xp_framework.easc.Handler handler
     */
    public void setHandler(Handler handler) {
        this.handler= handler;
    }

    /**
     * Thread's run method
     *
     * @access  public
     */
    @Override public void run() {
        this.stopped= false;   // We're running:)
        
        ServerContext ctx= new ServerContext();
        while (!this.stopped) {
            Socket accepted= null;
             
            try {
                accepted= this.socket.accept();

                // Create a new thread that will handle this client
                (new HandlerThread(this.handler, accepted, ctx)).start();
            } catch (IOException ignored) { }
        }
    }
    
    /**
     * Shut down the server
     *
     * @access  
     * @param   
     * @return  
     */
    public synchronized void shutdown() throws IOException {
        this.socket.close();
        this.stopped= true;
    }
}
